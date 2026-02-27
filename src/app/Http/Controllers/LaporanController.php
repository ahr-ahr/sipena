<?php

namespace App\Http\Controllers;

use App\Http\Requests\Laporan\StoreLaporanRequest;
use App\Services\Laporan\CreateLaporanService;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\KategoriLaporan;
use App\Models\TicketCategory;
use App\Models\LaporanAttachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Laporan\UpdateLaporanRequest;
use App\Enums\LaporanStatus;

class LaporanController extends Controller
{
    /**
     * QUERY BERSAMA (LIST + EXPORT)
     */
    private function laporanQuery(Request $request)
{
    $query = Laporan::query()
        ->with('kategori');

    $user = $request->user();

    // BATASI HANYA LAPORAN MILIK SENDIRI (SISWA)
    if ($user->tipe_user === \App\Enums\UserType::SISWA) {
        $query->where('pelapor_id', $user->id);
    }

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

    // FILTER KATEGORI
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
     * INDEX
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('laporan.index', [
                'kategoriLaporan'  => KategoriLaporan::orderBy('nama')->get(),
                'statusOptions'    => LaporanStatus::cases(),
            ]);
        }

        return response()->json(
            $this->laporanQuery($request)
                ->paginate(10)
                ->through(function ($laporan) {
                    return [
                        'uuid' => $laporan->uuid,
                        'kode_laporan' => $laporan->kode_laporan,
                        'judul' => $laporan->judul,
                        'created_at' => $laporan->created_at,
                        'kategori' => [
                            'id' => $laporan->kategori?->id,
                            'nama' => $laporan->kategori?->nama,
                        ],

                        'status' => [
                            'value' => $laporan->status->value,
                            'label' => $laporan->status->label(),
                            'color' => $laporan->status->color(),
                        ],
                    ];
                })
        );
    }

    /**
     * EXPORT EXCEL (IKUT FILTER)
     */
    public function exportExcel(Request $request)
    {
        $laporan = $this->laporanQuery($request)->get();

        return Excel::download(
            new LaporanExport($laporan, 'siswa'),
            'laporan.xlsx'
        );
    }

    /**
     * EXPORT PDF (IKUT FILTER)
     */
    public function exportPdf(Request $request)
    {
        $laporan = $this->laporanQuery($request)->get();

        $pdf = Pdf::loadView(
            'laporan.export.pdf',
            [
                'laporan' => $laporan,
                'role' => 'siswa'
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download('laporan.pdf');
    }

    /**
     * SHOW laporan (lengkap) — untuk modal
     */
    public function show(Laporan $laporan)
    {

        Gate::authorize('view', $laporan);

        $laporan->load([
            'kategori',
            'attachments',
            'pelapor',
            'waliKelas',
        ]);

        return response()->json([
    'data' => [
        'uuid' => $laporan->uuid,
        'kode_laporan' => $laporan->kode_laporan,
        'judul' => $laporan->judul,
        'deskripsi' => $laporan->deskripsi,
        'kategori_id' => $laporan->kategori_id,
        'created_at' => $laporan->created_at,

        'kategori' => [
            'id' => $laporan->kategori?->id,
            'nama' => $laporan->kategori?->nama,
        ],

        'current_role' => [
            'value' => $laporan->current_role?->value,
            'label' => $laporan->current_role?->label(),
        ],

        'status' => [
            'value' => $laporan->status->value,
            'label' => $laporan->status->label(),
            'color' => $laporan->status->color(),
        ],

        'attachments' => $laporan->attachments->map(function ($a) {
            return [
                'file_name' => $a->file_name,
                'url' => $a->url,
            ];
        }),
    ],
    'can_update' => Gate::allows('update', $laporan),
]);
    }

    public function store(
    StoreLaporanRequest $request,
    CreateLaporanService $service
) {
    Gate::authorize('create', Laporan::class);

    $result = $service->execute(
        $request->validated(),
        $request->file('attachments')
    );

    return response()->json([
        'message' => 'Laporan berhasil dikirim',
        'data'    => $result['laporan'],
        'ai_info' => $result['ai'],
    ], 201);
}

public function update(
    UpdateLaporanRequest $request,
    Laporan $laporan
) {
    Gate::authorize('update', $laporan);

    $laporan->update(
        $request->validated()
    );

    // simpan attachment baru
    if ($request->hasFile('attachments')) {
    foreach ($request->file('attachments') as $file) {

        $path = $file->store(
            "public/laporan/{$laporan->uuid}",
            'minio'
        );

        $laporan->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

    }
}

    return response()->json([
        'message' => 'Laporan berhasil diperbarui',
        'data'    => $laporan->fresh(['kategori','attachments']),
    ]);
}
}