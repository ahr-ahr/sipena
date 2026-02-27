<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Laporan;
use App\Models\Ticket;
use App\Enums\LaporanStatus;
use App\Enums\UserType;
use App\Exports\HistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewHistory', Laporan::class);

        if (!$request->expectsJson()) {
            return view('history.index', [
                'kategoriLaporan'  => \App\Models\KategoriLaporan::orderBy('nama')->get(),
                'laporanCategories'  => \App\Models\KategoriLaporan::orderBy('nama')->get(),
            ]);
        }

        return response()->json([
            'laporan'  => $this->laporanHistory($request),
            'tickets'  => $this->ticketHistory($request),
            'stats'    => $this->stats(),
            'timeline' => $this->timeline($request->period ?? 'month'),
        ]);
    }

    /* ================= BASE QUERY ================= */

    private function laporanBaseQuery()
    {
        $user = auth()->user();

        // siswa → laporan milik sendiri
        if ($user->tipe_user === UserType::SISWA) {
            return Laporan::where('pelapor_id', $user->id);
        }

        // pegawai → berdasarkan role
        $jabatan = strtolower(
            $user->jabatan->first()?->nama_jabatan ?? ''
        );

        $roleMap = [
            'kesiswaan' => 'kesiswaan',
            'sarpras' => 'sarpras',
            'bimbingan konseling' => 'bk',
            'tu' => 'tu',
            'guru' => 'guru',
            'wali kelas' => 'wali',
        ];

        $currentRole = $roleMap[$jabatan] ?? null;

        if ($currentRole) {
            return Laporan::where('current_role', $currentRole);
        }

        // kepala sekolah / IT → semua laporan
        return Laporan::query();
    }

    /* ================= HISTORY ================= */

    private function laporanHistory(Request $request)
    {
        return $this->laporanBaseQuery()
            ->whereIn('status', [
                LaporanStatus::SELESAI,
                LaporanStatus::DITOLAK,
            ])
            ->when($request->q, fn ($q) =>
                $q->where('judul', 'like', "%{$request->q}%")
                  ->orWhere('kode_laporan', 'like', "%{$request->q}%")
            )
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'laporan_page');
    }

    private function ticketHistory(Request $request)
    {
        return Ticket::where(fn ($q) =>
                $q->where('user_id', auth()->id())
                  ->orWhere('assigned_to', auth()->id())
            )
            ->whereIn('status', ['closed', 'resolved'])
            ->when($request->q, fn ($q) =>
                $q->where('title', 'like', "%{$request->q}%")
                  ->orWhere('ticket_number', 'like', "%{$request->q}%")
            )
            ->orderByDesc('closed_at')
            ->paginate(10, ['*'], 'ticket_page');
    }

    /* ================= STATS ================= */

    private function stats(): array
    {
        $laporanQuery = $this->laporanBaseQuery();

        return [
            'laporan' => [
                'selesai' => (clone $laporanQuery)
                    ->where('status', LaporanStatus::SELESAI)
                    ->count(),

                'ditolak' => (clone $laporanQuery)
                    ->where('status', LaporanStatus::DITOLAK)
                    ->count(),
            ],
            'ticket' => [
                'closed' => Ticket::where('status', 'closed')
                    ->where(fn ($q) =>
                        $q->where('user_id', auth()->id())
                          ->orWhere('assigned_to', auth()->id())
                    )->count(),

                'resolved' => Ticket::where('status', 'resolved')
                    ->where(fn ($q) =>
                        $q->where('user_id', auth()->id())
                          ->orWhere('assigned_to', auth()->id())
                    )->count(),
            ],
        ];
    }

    /* ================= TIMELINE ================= */

    private function timeline(string $period)
    {
        $format = match ($period) {
            'day'   => '%Y-%m-%d',
            'week'  => '%Y-%u',
            default => '%Y-%m',
        };

        $laporanBase = $this->laporanBaseQuery();

        $laporan = (clone $laporanBase)
            ->selectRaw("
                DATE_FORMAT(updated_at,'{$format}') label,
                COUNT(*) total
            ")
            ->whereIn('status', [
                LaporanStatus::SELESAI,
                LaporanStatus::DITOLAK
            ])
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $ticket = Ticket::selectRaw("
                DATE_FORMAT(closed_at,'{$format}') label,
                COUNT(*) total
            ")
            ->whereIn('status', ['closed', 'resolved'])
            ->where(fn ($q) =>
                $q->where('user_id', auth()->id())
                  ->orWhere('assigned_to', auth()->id())
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $labels = $laporan->pluck('label')
            ->merge($ticket->pluck('label'))
            ->unique()
            ->values();

        return [
            'labels'  => $labels,
            'laporan' => $labels->map(fn ($l) =>
                $laporan->firstWhere('label', $l)?->total ?? 0
            ),
            'ticket'  => $labels->map(fn ($l) =>
                $ticket->firstWhere('label', $l)?->total ?? 0
            ),
        ];
    }

    /* ================= EXPORT ================= */

    public function exportExcel()
    {
        return Excel::download(
            new HistoryExport(
                $this->buildRows(),
                $this->stats(),
                $this->timeline('month')
            ),
            'history.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        return Pdf::loadView('history.export.pdf', [
            'rows'      => $this->buildRows(),
            'stats'     => $this->stats(),
            'timeline'  => $this->timeline('month'),
            'printedAt' => now()->format('d-m-Y H:i'),
            'charts'   => $request->only(['donut','timeline','stacked'])
        ])
        ->setPaper('a4', 'landscape')
        ->download('history.pdf');
    }

    /* ================= BUILD ROWS ================= */

    private function buildRows()
    {
        $rows = collect();

        $this->laporanBaseQuery()
            ->whereIn('status', [
                LaporanStatus::SELESAI,
                LaporanStatus::DITOLAK
            ])
            ->get()
            ->each(fn ($l) => $rows->push([
                'jenis'   => 'Laporan',
                'kode'    => $l->kode_laporan,
                'judul'   => $l->judul,
                'status'  => $l->status->label(),
                'tanggal' => $l->updated_at->format('d/m/Y'),
            ]));

        Ticket::whereIn('status', ['closed', 'resolved'])
            ->where(fn ($q) =>
                $q->where('user_id', auth()->id())
                  ->orWhere('assigned_to', auth()->id())
            )
            ->get()
            ->each(fn ($t) => $rows->push([
                'jenis'   => 'Tiket',
                'kode'    => $t->ticket_number,
                'judul'   => $t->title,
                'status'  => ucfirst($t->status),
                'tanggal' => optional($t->closed_at)->format('d/m/Y'),
            ]));

        return $rows;
    }
}
