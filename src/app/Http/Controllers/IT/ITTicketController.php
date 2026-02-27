<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Auth;
use App\Enums\TicketStatus;
use Barryvdh\DomPDF\Facade\Pdf;

class ITTicketController extends Controller
{
    public function __construct()
    {
        Gate::authorize('manage-tickets');
    }

    /**
     * Base query untuk IT (lihat semua tiket)
     */
    private function baseQuery(Request $request)
    {
        $query = Ticket::query()
            ->with(['laporan.kategori', 'user', 'assignee']);

        if ($search = trim($request->q)) {
            $query->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")
                   ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->category_id) {
            $query->whereHas('laporan', function ($q) use ($categoryId) {
                $q->where('kategori_id', $categoryId);
            });
        }

        $allowedSort = ['created_at', 'title', 'priority', 'status'];

        $sortBy = in_array($request->sort_by, $allowedSort)
            ? $request->sort_by
            : 'created_at';

        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir);
    }

    /**
     * LIST TICKETS (IT)
     */
    public function index(Request $request)
{
    if (! $request->expectsJson()) {
        return view('it.tickets.index', [

            'itUsers' => \App\Models\User::whereHas('jabatan', function ($q) {
    $q->where('nama_jabatan', 'like', '%IT%');
})->get(),
            'laporanSelesai' => \App\Models\Laporan::query()
        ->with('kategori')
        ->where('status', \App\Enums\LaporanStatus::SELESAI)
        ->doesntHave('ticket')
        ->latest()
        ->get(),
        'statusOptions' => TicketStatus::cases(),
        ]);
    }

    $tickets = $this->baseQuery($request)->paginate(15);

    return response()->json([
        'data' => $tickets->items(),
        'meta' => [
            'current_page' => $tickets->currentPage(),
            'last_page'    => $tickets->lastPage(),
            'total'        => $tickets->total(),
        ],
    ]);
}

    /**
     * SHOW DETAIL TICKET
     */
    public function show(Request $request, Ticket $ticket)
    {
        $ticket->load([
            'laporan.kategori',
            'user',
            'assignee',
            'comments.user',
            'attachments',
        ]);

        return response()->json([
            'data' => $ticket,
        ]);
    }

    public function comment(Request $request, Ticket $ticket)
{
    $request->validate([
        'message' => ['required', 'string'],
    ]);

    $comment = TicketComment::create([
        'ticket_id' => $ticket->id,
        'user_id'   => Auth::id(),
        'message'   => $request->message,
        'is_internal' => false,
    ]);

    $old = $ticket->status;

    // jika tiket masih open → berarti IT mulai menangani
    if ($ticket->status === 'open') {
        $ticket->update([
            'status' => 'in_progress',
            'assigned_to' => Auth::id(),
        ]);

        $ticket->statusLogs()->create([
            'old_status' => $old,
            'new_status' => 'in_progress',
            'changed_by' => Auth::id(),
        ]);
    }

    // jika tiket progress → berarti IT menunggu respon user
    elseif ($ticket->status === 'in_progress') {
        $ticket->update([
            'status' => 'waiting',
        ]);

        $ticket->statusLogs()->create([
            'old_status' => $old,
            'new_status' => 'waiting',
            'changed_by' => Auth::id(),
        ]);
    }

    return response()->json([
        'message' => 'Komentar dikirim',
        'data' => $comment,
    ]);
}

    /**
     * RESOLVE TICKET
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $ticket->update([
            'status'    => 'resolved',
            'closed_at' => now(),
        ]);

        if (! $request->expectsJson()) {
            return back()->with('success', 'Tiket diselesaikan');
        }

        return response()->json([
            'message' => 'Tiket berhasil diselesaikan',
            'data'    => [
                'id'     => $ticket->id,
                'status' => $ticket->status,
            ],
        ]);
    }

    public function print(Ticket $ticket)
{
    $ticket->load([
        'laporan.kategori',
        'user',
        'assignee',
    ]);

    $pdf = Pdf::loadView('it.tickets.print', [
        'ticket' => $ticket,
        'printedAt' => now()->format('d-m-Y H:i'),
    ])->setPaper('a4', 'portrait');

    return $pdf->download('surat-kerja-' . $ticket->ticket_number . '.pdf');
}
}
