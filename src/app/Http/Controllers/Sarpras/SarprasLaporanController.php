<?php

namespace App\Http\Controllers\Sarpras;

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

class SarprasLaporanController extends Controller
{
    public function __construct()
    {
        Gate::authorize('sarpras-laporan');
    }


    private function baseQuery(Request $request)
{
    $query = Laporan::query()
        ->with(['pelapor.siswaProfile.kelas', 'kategori'])
        ->where('current_role', 'sarpras');

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
            return view('sarpras.laporan.index', [
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
     * SET PROSES
     */
    public function proses(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::DIPROSES_SARPRAS,
        ]);

        $this->notifyPelapor(
            $laporan,
            'Laporan Diproses',
            "Laporan {$laporan->kode_laporan} sedang diproses oleh sarpras"
        );

        return response()->json([
            'message' => 'Laporan sedang diproses',
        ]);
    }

    /**
     * SET SELESAI
     */
    public function selesai(Request $request, Laporan $laporan)
{
    $data = $request->validate([
        'items' => ['required', 'array', 'min:1'],
        'items.*.nama_item' => ['required', 'string'],
        'items.*.qty' => ['required', 'numeric', 'min:1'],
        'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
    ]);

    $total = 0;

    foreach ($data['items'] as $item) {
        $total += $item['qty'] * $item['harga_satuan'];
    }

    $anggaran = $laporan->anggaran()->create([
        'uuid'        => \Str::uuid(),
        'dibuat_oleh' => auth()->id(),
        'total_biaya' => $total,
        'status'      => \App\Enums\AnggaranStatus::DRAFT,
    ]);

    foreach ($data['items'] as $item) {
        $subtotal = $item['qty'] * $item['harga_satuan'];

        $anggaran->details()->create([
            'nama_item'    => $item['nama_item'],
            'qty'          => $item['qty'],
            'harga_satuan' => $item['harga_satuan'],
            'subtotal'     => $subtotal,
        ]);
    }

    $laporan->update([
        'status' => LaporanStatus::DISETUJUI_SARPRAS,
    ]);

    $this->notifyPelapor(
        $laporan,
        'Laporan Disetujui Sarpras',
        "Laporan {$laporan->kode_laporan} telah disetujui oleh sarpras"
    );

    $this->notifyTU($laporan);

    return response()->json([
        'message' => 'Anggaran berhasil dibuat',
    ]);
}

public function exportExcel(Request $request)
{
    $laporan = $this->baseQuery($request)
        ->with(['pelapor.siswaProfile', 'kategori'])
        ->get();

    return Excel::download(
        new LaporanExport($laporan),
        'laporan-sarpras.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $laporan = $this->baseQuery($request)
        ->with(['pelapor.siswaProfile', 'kategori'])
        ->get();

    $pdf = Pdf::loadView('sarpras.laporan.export.pdf', [
        'laporan' => $laporan
    ])->setPaper('a4', 'portrait');

    return $pdf->download('laporan-sarpras.pdf');
}

    private function notifyTU(Laporan $laporan): void
{
    $notification = Notification::create([
        'uuid'       => Str::uuid(),
        'judul'      => 'Laporan Siap Diproses TU',
        'pesan'      => "Laporan {$laporan->kode_laporan} menunggu tindak lanjut TU",
        'tipe'       => 'laporan',
        'laporan_id' => $laporan->id,
    ]);

    $tuUsers = \App\Models\User::query()
        ->where('tipe_user', \App\Enums\UserType::PEGAWAI)
        ->whereHas('jabatan', fn ($q) => $q->where('nama_jabatan', 'TU'))
        ->get();

    foreach ($tuUsers as $tu) {
        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id'         => $tu->id,
            'is_read'         => false,
        ]);

        event(new NotificationCreated(
            $notification,
            $tu->id
        ));
    }
}

    /**
     * HELPER: NOTIFIKASI KE PELAPOR
     */
    private function notifyPelapor(Laporan $laporan, string $judul, string $pesan): void
{
    $notification = Notification::create([
        'uuid'       => Str::uuid(),
        'judul'      => $judul,
        'pesan'      => $pesan,
        'tipe'       => 'laporan',
        'laporan_id' => $laporan->id,
    ]);

    // =========================
    // 1. NOTIF KE SISWA (PELAPOR)
    // =========================
    NotificationRecipient::create([
        'notification_id' => $notification->id,
        'user_id'         => $laporan->pelapor_id,
        'is_read'         => false,
    ]);

    event(new NotificationCreated(
        $notification,
        $laporan->pelapor_id
    ));

    // =========================
    // 2. NOTIF KE WALI KELAS
    // =========================
    $waliUsers = $laporan->pelapor
        ?->siswaProfile
        ?->kelas
        ?->waliUser ?? collect();

    foreach ($waliUsers as $wali) {
        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id'         => $wali->id,
            'is_read'         => false,
        ]);

        event(new NotificationCreated(
            $notification,
            $wali->id
        ));
    }
}

}
