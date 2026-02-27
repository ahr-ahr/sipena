<?php

namespace App\Http\Controllers\Kesiswaan;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\LaporanStatus;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Events\NotificationCreated;
use Illuminate\Support\Str;

class KesiswaanLaporanController extends Controller
{
    public function __construct()
    {
        Gate::authorize('kesiswaan-laporan');
    }

    private function baseQuery(Request $request)
    {
        $query = Laporan::query()
            ->with(['pelapor.siswaProfile.kelas', 'kategori'])
            ->where('current_role', 'kesiswaan');

        // SEARCH
        if ($search = trim($request->q)) {
            $query->where(function ($qq) use ($search) {
                $qq->where('judul', 'like', "%{$search}%")
                   ->orWhere('kode_laporan', 'like', "%{$search}%");
            });
        }

        // FILTER KATEGORI
        if ($kategori = $request->kategori_id) {
            $query->where('kategori_id', $kategori);
        }

        // FILTER STATUS
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        // SORT
        $allowedSort = ['created_at', 'judul', 'status'];

        $sortBy = in_array($request->sort_by, $allowedSort)
            ? $request->sort_by
            : 'created_at';

        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir);
    }

    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('kesiswaan.laporan.index', [
                'kategoriLaporan' => \App\Models\KategoriLaporan::orderBy('nama')->get(),
                'statusOptions'   => LaporanStatus::cases(),
            ]);
        }

        $laporan = $this->baseQuery($request)->paginate(10);

        return response()->json([
            'data' => $laporan->items(),
            'meta' => [
                'current_page' => $laporan->currentPage(),
                'last_page'    => $laporan->lastPage(),
                'total'        => $laporan->total(),
            ],
        ]);
    }

    public function show(Laporan $laporan)
    {
        $laporan->load([
            'pelapor.siswaProfile.kelas',
            'kategori',
            'attachments',
        ]);

        return response()->json([
            'data' => $laporan,
        ]);
    }

    /**
     * TINDAK LANGSUNG OLEH KESISWAAN
     */
    public function tindakLangsung(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::SELESAI,
            'current_role' => 'kesiswaan',
        ]);

        $this->notifyPelapor(
            $laporan,
            'Aspirasi Ditindak Kesiswaan',
            "Aspirasi {$laporan->kode_laporan} telah ditindak oleh kesiswaan"
        );

        return response()->json([
            'message' => 'Aspirasi berhasil ditindak langsung',
        ]);
    }

    /**
     * TERUSKAN KE SARPRAS
     */
    public function teruskanKeSarpras(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::DIPROSES,
            'current_role' => 'sarpras',
        ]);

        $this->notifySarpras($laporan);

        $this->notifyPelapor(
            $laporan,
            'Aspirasi Diteruskan',
            "Aspirasi {$laporan->kode_laporan} diteruskan ke sarpras"
        );

        return response()->json([
            'message' => 'Aspirasi diteruskan ke sarpras',
        ]);
    }

    /**
     * TOLAK LAPORAN
     */
    public function tolak(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::DITOLAK,
            'current_role' => 'kesiswaan',
        ]);

        $this->notifyPelapor(
            $laporan,
            'Aspirasi Ditolak',
            "Aspirasi {$laporan->kode_laporan} ditolak oleh kesiswaan"
        );

        return response()->json([
            'message' => 'Aspirasi ditolak',
        ]);
    }

    private function notifySarpras(Laporan $laporan): void
    {
        $notification = Notification::create([
            'uuid'       => Str::uuid(),
            'judul'      => 'Aspirasi Baru',
            'pesan'      => "Aspirasi {$laporan->kode_laporan} menunggu penanganan sarpras",
            'tipe'       => 'laporan',
            'laporan_id' => $laporan->id,
        ]);

        $users = \App\Models\User::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'Sarpras');
        })->get();

        foreach ($users as $user) {
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

    private function notifyPelapor(Laporan $laporan, string $judul, string $pesan): void
    {
        $notification = Notification::create([
            'uuid'       => Str::uuid(),
            'judul'      => $judul,
            'pesan'      => $pesan,
            'tipe'       => 'laporan',
            'laporan_id' => $laporan->id,
        ]);

        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id'         => $laporan->pelapor_id,
            'is_read'         => false,
        ]);

        event(new NotificationCreated(
            $notification,
            $laporan->pelapor_id
        ));
    }
}
