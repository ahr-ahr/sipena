<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\LaporanStatus;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class BKLaporanController extends Controller
{
    public function __construct()
    {
        Gate::authorize('bk-laporan');
    }

    private function allowedStatuses(): array
{
    return [
        LaporanStatus::MENUNGGU_BK,
        LaporanStatus::DIPROSES_BK,
        LaporanStatus::SELESAI,
        LaporanStatus::DITOLAK_BK,
    ];
}

    private function baseQuery(Request $request)
    {
        $query = Laporan::query()
            ->with(['pelapor.siswaProfile.kelas', 'kategori'])
            ->whereIn('status', $this->allowedStatuses());

        if ($search = trim($request->q)) {
            $query->where(function ($qq) use ($search) {
                $qq->where('judul', 'like', "%{$search}%")
                   ->orWhere('kode_laporan', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

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
            return view('bk.laporan.index', [
                'statusOptions' => $this->allowedStatuses(),
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
        ]);

        return response()->json([
            'data' => $laporan,
        ]);
    }

    public function proses(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::DIPROSES_BK,
        ]);

        return response()->json([
            'message' => 'Laporan diproses BK',
        ]);
    }

    public function selesai(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::SELESAI,
        ]);

        return response()->json([
            'message' => 'Laporan selesai',
        ]);
    }

    public function tolak(Laporan $laporan)
    {
        $laporan->update([
            'status' => LaporanStatus::DITOLAK_BK,
        ]);

        return response()->json([
            'message' => 'Laporan ditolak',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $laporan = $this->baseQuery($request)
            ->with(['pelapor.siswaProfile', 'kategori'])
            ->get();

        return Excel::download(
            new LaporanExport($laporan),
            'laporan-bk.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $laporan = $this->baseQuery($request)
            ->with(['pelapor.siswaProfile', 'kategori'])
            ->get();

        $pdf = Pdf::loadView('bk.laporan.export.pdf', [
            'laporan' => $laporan
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-bk.pdf');
    }
}
