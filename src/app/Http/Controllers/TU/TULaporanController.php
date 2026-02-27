<?php

namespace App\Http\Controllers\TU;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\LaporanStatus;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Events\NotificationCreated;
use Illuminate\Support\Str;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TULaporanController extends Controller
{
    public function __construct()
    {
        Gate::authorize('tu-laporan');
    }

    private function allowedStatuses(): array
{
    return [
        LaporanStatus::DIPROSES_SARPRAS,
        LaporanStatus::DISETUJUI_SARPRAS,
        LaporanStatus::DISETUJUI_TU,
        LaporanStatus::DITOLAK_TU,
        LaporanStatus::SELESAI,
        LaporanStatus::DITOLAK_KEPSEK,
    ];
}

    private function baseQuery(Request $request)
{
    $query = Laporan::query()
        ->with(['pelapor.siswaProfile.kelas', 'kategori'])
        ->whereIn('status', $this->allowedStatuses());

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
        return view('tu.laporan.index', [
            'kategoriLaporan' => \App\Models\KategoriLaporan::orderBy('nama')->get(),
            'statusOptions'   => $this->allowedStatuses(),
        ]);
    }

    $laporan = $this->baseQuery($request)->paginate(10);

    return response()->json([
        'data' => $laporan->items(),
        'meta' => [
            'current_page' => $laporan->currentPage(),
            'last_page'    => $laporan->lastPage(),
        ],
    ]);
}

    public function show(Laporan $laporan)
    {
        $laporan->load([
            'pelapor.siswaProfile.kelas',
            'kategori',
            'attachments',
            'anggaran.details',
        ]);

        return response()->json([
            'data' => $laporan,
        ]);
    }

    /**
     * SETUJUI TU
     */
    public function setujui(Laporan $laporan)
{
    $laporan->update([
        'status' => LaporanStatus::DISETUJUI_TU,
    ]);

    $this->notifyStakeholders(
        $laporan,
        'Laporan Disetujui TU',
        "Laporan {$laporan->kode_laporan} disetujui oleh TU"
    );

    return response()->json([
        'message' => 'Laporan disetujui oleh TU',
    ]);
}

    /**
     * TOLAK TU
     */
    public function tolak(Laporan $laporan)
{
    $laporan->update([
        'status' => LaporanStatus::DITOLAK_TU,
    ]);

    $this->notifyStakeholders(
        $laporan,
        'Laporan Ditolak TU',
        "Laporan {$laporan->kode_laporan} ditolak oleh TU"
    );

    return response()->json([
        'message' => 'Laporan ditolak oleh TU',
    ]);
}

public function exportExcel(Request $request)
{
    $laporan = $this->baseQuery($request)
        ->with(['pelapor.siswaProfile', 'kategori'])
        ->get();

    return Excel::download(
        new LaporanExport($laporan),
        'laporan-tu.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $laporan = $this->baseQuery($request)
        ->with(['pelapor.siswaProfile', 'kategori'])
        ->get();

    $pdf = Pdf::loadView('tu.laporan.export.pdf', [
        'laporan' => $laporan
    ])->setPaper('a4', 'portrait');

    return $pdf->download('laporan-tu.pdf');
}

    private function notifyStakeholders(Laporan $laporan, string $judul, string $pesan): void
{
    $notification = Notification::create([
        'uuid'       => Str::uuid(),
        'judul'      => $judul,
        'pesan'      => $pesan,
        'tipe'       => 'laporan',
        'laporan_id' => $laporan->id,
    ]);

    $recipients = collect();

    // =====================
    // 1. PELAPOR (SISWA)
    // =====================
    $recipients->push($laporan->pelapor_id);

    // =====================
    // 2. WALI KELAS
    // =====================
    $waliUsers = $laporan->pelapor
        ?->siswaProfile
        ?->kelas
        ?->waliUser ?? collect();

    foreach ($waliUsers as $wali) {
        $recipients->push($wali->id);
    }

    // =====================
    // 3. SARPRAS
    // =====================
    $sarprasUsers = \App\Models\User::query()
        ->where('tipe_user', \App\Enums\UserType::PEGAWAI)
        ->whereHas('jabatan', fn ($q) =>
            $q->where('nama_jabatan', 'Sarpras')
        )
        ->pluck('id');

    // =====================
    // 4. KEPALA SEKOLAH
    // =====================
    $kepsekUsers = \App\Models\User::query()
        ->where('tipe_user', \App\Enums\UserType::PEGAWAI)
        ->whereHas('jabatan', fn ($q) =>
            $q->where('nama_jabatan', 'Kepala Sekolah')
        )
        ->pluck('id');

    // =====================
    // GABUNG SEMUA RECIPIENT
    // =====================
    $recipients = $recipients
        ->merge($sarprasUsers)
        ->merge($kepsekUsers)
        ->unique();

    // =====================
    // SIMPAN NOTIF
    // =====================
    foreach ($recipients as $userId) {
        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id'         => $userId,
            'is_read'         => false,
        ]);

        event(new NotificationCreated(
            $notification,
            $userId
        ));
    }
}
}
