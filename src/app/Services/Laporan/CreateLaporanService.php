<?php

namespace App\Services\Laporan;

use App\Models\Laporan;
use App\Models\LaporanAttachment;
use App\Models\AuditLog;
use App\Enums\LaporanStatus;
use App\Enums\LaporanRole;
use App\Services\KodeGeneratorService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Events\NotificationCreated;
use App\Models\NotificationRecipient;
use App\Services\Laporan\LaporanModerationService;
use App\Services\Laporan\LaporanAIService;
use App\Models\KategoriLaporan;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Support\JabatanMap;

class CreateLaporanService
{
    public function __construct(
        protected KodeGeneratorService $kodeGenerator,
        protected LaporanModerationService $moderation,
        protected LaporanAIService $ai
    ) {}

    public function execute(array $data, ?array $attachments = null): array
    {
        $kategori = KategoriLaporan::find($data['kategori_id']);
        $kategoriNama = $kategori?->nama ?? 'Lainnya';
        $judul = $data['judul'] ?? '';
        $deskripsi = $data['deskripsi'] ?? '';

        $text = $judul . ' ' . $deskripsi;

        // Moderation
        if ($this->moderation->isBlocked($text)) {
            throw ValidationException::withMessages([
                'deskripsi' => 'Laporan terdeteksi mengandung konten tidak pantas.',
            ]);
        }

        // AI Classification
        $aiResult = $this->ai->classify($kategoriNama, $judul, $deskripsi);

        if (
            in_array($aiResult['label'], ['spam', 'bercanda']) &&
            ($aiResult['score'] ?? 0) > 0.8
        ) {
            throw ValidationException::withMessages([
                'deskripsi' => 'Gak lucu gua cari alamat lu, isi laporan yang bener',
            ]);
        }

        if (
            ($aiResult['kategori_sesuai'] ?? true) === false
            && ($aiResult['score'] ?? 0) > 0.7
        ) {
            $kategoriBaru = KategoriLaporan::where(
                'nama',
                $aiResult['saran_kategori']
            )->first();

            if ($kategoriBaru) {
                $data['kategori_id'] = $kategoriBaru->id;
                $kategoriNama = $kategoriBaru->nama;
            }
        }

        $priority = match ($aiResult['label']) {
            'ancaman' => 'darurat',
            'bullying' => 'tinggi',
            default => 'normal',
        };

        return DB::transaction(function () use ($data, $attachments, $aiResult, $priority, $kategoriNama) {

            $statusAwal = LaporanStatus::MENUNGGU;
            $currentRole = $this->resolveInitialRole($kategoriNama);

            $laporan = Laporan::create([
                'uuid'          => Str::uuid(),
                'kode_laporan'  => $this->kodeGenerator->generateLaporan(),
                'judul'         => $data['judul'],
                'deskripsi'     => $data['deskripsi'],
                'kategori_id'   => $data['kategori_id'],
                'pelapor_id'    => auth()->id(),
                'mapel_id' => $data['mapel_id'] ?? null,
                'status'        => $statusAwal,
                'current_role'  => $currentRole,
                'ai_label'      => $aiResult['label'],
                'ai_score'      => $aiResult['score'],
                'priority'      => $priority,
            ]);

            // Attachments
            if ($attachments) {
                foreach ($attachments as $file) {
                    $this->storeAttachment($laporan, $file);
                }
            }

            // Audit log
            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create_laporan',
                'target_type' => 'laporan',
                'target_id'   => $laporan->id,
                'description' => "Siswa membuat laporan {$laporan->kode_laporan}",
                'ip_address'  => request()->ip(),
            ]);

            // Kirim notifikasi sesuai role
            $this->sendInitialNotification($laporan, $currentRole);

            return [
                'laporan' => $laporan->load('attachments'),
                'ai' => [
                    'kategori_diubah' =>
                        ($aiResult['kategori_sesuai'] ?? true) === false,
                    'saran_kategori' =>
                        $aiResult['saran_kategori'] ?? null,
                ]
            ];
        });
    }

    /**
     * Tentukan role awal berdasarkan kategori
     */
    protected function resolveInitialRole(string $kategoriNama): LaporanRole
    {
        return match ($kategoriNama) {
            'Pengaduan Fasilitas' => LaporanRole::SARPRAS,
            'Pengaduan Disiplin' => LaporanRole::BK,
            'Pengaduan Akademik' => LaporanRole::GURU,
            'Aspirasi Siswa' => LaporanRole::KESISWAAN,
            'Laporan Keamanan' => LaporanRole::KESISWAAN,
            default => LaporanRole::WALI,
        };
    }

    /**
     * Kirim notifikasi ke role tujuan awal
     */
    protected function sendInitialNotification(Laporan $laporan, LaporanRole $role): void
{
    // 1. Akademik → langsung ke guru mapel
    if ($role === LaporanRole::GURU) {

        if (!$laporan->mapel_id) {
            return;
        }

        $siswa = auth()->user()->siswaProfile;
        $kelasId = $siswa?->kelas_id;

        if (!$kelasId) {
            return;
        }

        $users = User::whereHas('pegawaiProfile.kelasMapel', function ($q) use ($kelasId, $laporan) {
            $q->where('kelas_id', $kelasId)
              ->where('mapel_id', $laporan->mapel_id);
        })->get();

        $judul = 'Laporan Akademik Baru';
        $pesan = "Laporan {$laporan->kode_laporan} terkait mata pelajaran menunggu penanganan Anda";
    }

    elseif ($role === LaporanRole::WALI) {

        $wali = auth()->user()
            ->siswaProfile
            ?->kelas
            ?->waliUser
            ?->first();

        if (!$wali) {
            return;
        }

        $users = collect([$wali]);
        $judul = 'Laporan Baru dari Siswa';
        $pesan = "Laporan {$laporan->kode_laporan} menunggu persetujuan Anda";
    }

    // 3. Role lain (BK, Sarpras, Kesiswaan, dll)
    else {

        $jabatanNama = JabatanMap::getName($role);

        $users = User::whereHas('jabatan', function ($q) use ($jabatanNama) {
            $q->where('nama_jabatan', $jabatanNama);
        })->get();

        $judul = 'Laporan Baru';
        $pesan = "Laporan {$laporan->kode_laporan} menunggu penanganan {$jabatanNama}";
    }

    // Kirim notifikasi
    foreach ($users as $user) {
        $notification = Notification::create([
            'uuid'       => Str::uuid(),
            'judul'      => $judul,
            'pesan'      => $pesan,
            'tipe'       => 'laporan',
            'laporan_id' => $laporan->id,
        ]);

        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id'         => $user->id,
            'is_read'         => false,
        ]);

        event(new NotificationCreated(
            $notification,
            $user->id
        ));
    }
}

    protected function storeAttachment(Laporan $laporan, UploadedFile $file): void
    {
        $path = $file->store(
            "public/laporan/{$laporan->uuid}",
            'minio'
        );

        LaporanAttachment::create([
            'uuid'       => Str::uuid(),
            'laporan_id' => $laporan->id,
            'file_path'  => $path,
            'file_name'  => $file->getClientOriginalName(),
            'mime_type'  => $file->getClientMimeType(),
            'file_size'  => $file->getSize(),
        ]);
    }
}
