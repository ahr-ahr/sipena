<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Laporan\ApproveLaporanByWaliService;
use App\Services\Laporan\RejectLaporanByWaliService;
use App\Enums\LaporanStatus;
use App\Models\KategoriLaporan;

class LaporanController extends Controller
{

    /**
     * QUERY BERSAMA (LIST + EXPORT)
     */
    private function laporanQuery(Request $request)
{
    $allowedKategori = KategoriLaporan::whereIn('nama', [
        'Lainnya',
        'Pengaduan Akademik'
    ])->pluck('id');

    $wali = $request->user();

    $query = Laporan::query()
        ->whereHas('pelapor.siswaProfile.kelas.waliUser', function ($q) use ($wali) {
            $q->where('users.id', $wali->id);
        })
        ->whereIn('kategori_id', $allowedKategori)
        ->with([
            'kategori',
            'pelapor.siswaProfile.kelas',
            'attachments'
        ]);

    // SEARCH
    if ($q = $request->q) {
        $query->where(function ($qq) use ($q) {
            $qq->where('judul', 'like', "%{$q}%")
               ->orWhere('kode_laporan', 'like', "%{$q}%");
        });
    }

    // FILTER STATUS
    if ($status = $request->status) {
        $query->where('status', $status);
    }

    // FILTER KATEGORI (tetap aman karena sudah dibatasi allowedKategori)
    if ($kategori = $request->kategori_id) {
        $query->where('kategori_id', $kategori);
    }

    // SORT (AMAN)
    $allowedSort = ['created_at', 'judul', 'status'];
    $sortBy = in_array($request->sort_by, $allowedSort)
        ? $request->sort_by
        : 'created_at';

    $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

    return $query->orderBy($sortBy, $sortDir);
}

    /**
     * INDEX (HYBRID)
     */
    public function index(Request $request)
    {
        Gate::authorize('wali-laporan');

        // HTML
        if (!$request->expectsJson()) {
            return view('wali.laporan.index', [
                'kategoriLaporan' => KategoriLaporan::whereIn('nama', [
                    'Lainnya',
                    'Pengaduan Akademik'
                ])->orderBy('nama')->get(),
                'statusOptions'   => LaporanStatus::cases(),
                'laporan'         => $this->laporanQuery($request)
                    ->paginate(10)
                    ->withQueryString(),
            ]);
        }

        // JSON
        return response()->json(
            $this->laporanQuery($request)->paginate(10)
        );
    }

    /**
     * EXPORT EXCEL
     */
    public function exportExcel(Request $request)
    {
        $laporan = $this->laporanQuery($request)->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanExport($laporan, 'wali'),
            'laporan-wali.xlsx'
        );
    }

    /**
     * EXPORT PDF
     */
    public function exportPdf(Request $request)
{
    $laporan = $this->laporanQuery($request)
        ->with('kategori')
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'laporan.export.pdf',
        [
            'laporan' => $laporan,
            'role' => 'wali'
        ]
    )
    ->setPaper('a4', 'landscape')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    return $pdf->download('laporan-wali.pdf');
}

    /**
     * APPROVE (HYBRID)
     */
    public function approve(
        Request $request,
        Laporan $laporan,
        ApproveLaporanByWaliService $service
    ) {
        Gate::authorize('wali-laporan');

        $service->execute($laporan, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Laporan disetujui',
                'status'  => $laporan->fresh()->status->value,
            ]);
        }

        return back()->with('success', 'Laporan disetujui');
    }

    /**
     * REJECT (HYBRID)
     */
    public function reject(
        Request $request,
        Laporan $laporan,
        RejectLaporanByWaliService $service
    ) {
        Gate::authorize('wali-laporan');

        $service->execute(
            $laporan,
            $request->user(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Laporan ditolak',
                'status'  => $laporan->fresh()->status->value,
            ]);
        }

        return back()->with('success', 'Laporan ditolak');
    }
}
