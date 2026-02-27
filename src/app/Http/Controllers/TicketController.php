<?php

namespace App\Http\Controllers;

use App\Models\{
    Ticket,
    User,
    Notification,
    NotificationRecipient
};
use App\Enums\UserType;
use App\Events\{
    NotificationCreated,
    NotificationUnreadCountUpdated
};
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use App\Exports\TicketExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Ticket\AddTicketCommentService;

class TicketController extends Controller
{
    private function baseQuery(Request $request)
{
    $query = Ticket::query()
        ->with(['category', 'user', 'assignee'])
        ->where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('assigned_to', auth()->id());
        });

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
        $query->where('category_id', $categoryId);
    }

    $allowedSort = ['created_at', 'title', 'priority', 'status'];

    $sortBy = in_array($request->sort_by, $allowedSort)
        ? $request->sort_by
        : 'created_at';

    $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

    return $query->orderBy($sortBy, $sortDir);
}
    /**
     * LIST TICKET
     */
    public function index(Request $request)
{

    if (!$request->expectsJson()) {
        return view('tickets.index', [
            'kategoriLaporan'  => \App\Models\KategoriLaporan::orderBy('nama')->get(),
        ]);
    }

    return response()->json(
        $this->baseQuery($request)->paginate(10)
    );
}

    /**
     * SHOW DETAIL (MODAL)
     */
    public function show(Ticket $ticket)
{
     
    $ticket->load([
        'category',
        'user',
        'assignee',
        'comments.user',
        'attachments',
        'statusLogs.changer',
    ]);

    return response()->json([
        'data' => $ticket,
        'can_update' => Gate::allows('update', $ticket),
    ]);
}


    public function exportExcel(Request $request)
{
    return Excel::download(
        new TicketExport(
            $this->baseQuery($request)->get()
        ),
        'tickets.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $tickets = $this->baseQuery($request)->get();

    $pdf = Pdf::loadView('tickets.export.pdf', [
        'tickets' => $tickets,
        'printedAt' => now()->format('d-m-Y H:i'),
    ])->setPaper('a4', 'landscape');

    return $pdf->download('tickets.pdf');
}

    /**
     * STORE
     */
    public function store(StoreTicketRequest $request)
{
    
    Gate::authorize('create-ticket');

    $laporan = \App\Models\Laporan::where('id', $request->laporan_id)
        ->where('status', \App\Enums\LaporanStatus::SELESAI)
        ->firstOrFail();

    $ticket = Ticket::create([
    'ticket_number' => 'TCK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
    'user_id'       => auth()->id(),
    'laporan_id'    => $laporan->id,

    'assigned_to'   => $request->assigned_to ?: null,
    'external_vendor' => $request->external_vendor ?: null,
    'external_notes'  => $request->external_notes,

    'category_id'   => $laporan->kategori_id,

    'priority'      => $request->priority,
    'title'         => $request->title,
    'description'   => $request->description,
    'status'        => $request->assigned_to ? 'open' : 'waiting_vendor',
]);


    $ticket->statusLogs()->create([
        'old_status' => null,
        'new_status' => $ticket->status,
        'changed_by' => auth()->id(),
    ]);

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {

            $path = $file->store("public/tickets/{$ticket->id}", 'minio');

            $ticket->attachments()->create([
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_size'   => $file->getSize(),
                'mime_type'   => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    if ($ticket->assigned_to) {
        $this->notifyIT($ticket);
    }

    return response()->json([
        'message'   => 'Tiket berhasil dibuat',
        'ticket_id' => $ticket->id,
    ]);
}

    /**
     * UPDATE TICKET (EDIT)
     */
    public function update(Request $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
        ]);

        $ticket->update($validated);

        return response()->json([
            'message' => 'Tiket berhasil diperbarui',
        ]);
    }

    /**
     * COMMENT (USER)
     */
    public function comment(
    Request $request,
    Ticket $ticket,
    AddTicketCommentService $service
) {
    $request->validate([
        'message' => ['required', 'string'],
        'attachment' => ['nullable', 'file', 'max:5120'],
    ]);

    $comment = $service->execute(
        ticket: $ticket,
        user: auth()->user(),
        message: $request->message,
        isInternal: false
    );

    // simpan file jika ada
    if ($request->hasFile('attachment')) {
    $file = $request->file('attachment');

    $filename = time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $file->getClientOriginalExtension();

    $path = $file->storeAs(
        "public/tickets/{$ticket->id}/comments",
        $filename,
        'minio'
    );

    $ticket->attachments()->create([
        'file_name'   => $file->getClientOriginalName(),
        'file_path'   => $path,
        'size'        => $file->getSize(),
        'mime_type'   => $file->getMimeType(),
        'uploaded_by' => auth()->id(),
    ]);
}

    // jika user membalas dan tiket sedang waiting
    if ($ticket->status === 'waiting') {
        $old = $ticket->status;

        $ticket->update([
            'status' => 'in_progress'
        ]);

        $ticket->statusLogs()->create([
            'old_status' => $old,
            'new_status' => 'in_progress',
            'changed_by' => auth()->id(),
        ]);
    }

    return response()->json([
        'message' => 'Komentar berhasil dikirim',
        'data'    => $comment->load('user'),
    ]);
}

    /**
     *
     * HELPER: NOTIFIKASI IT
     *
     */
    private function notifyIT(Ticket $ticket): void
    {
        $notification = Notification::create([
            'judul'     => 'Tiket Baru Masuk',
            'pesan'     => "Tiket {$ticket->ticket_number} membutuhkan penanganan",
            'tipe'      => 'ticket',
            'ticket_id' => $ticket->id,
        ]);

        $itUsers = User::query()
            ->where('tipe_user', UserType::PEGAWAI)
            ->whereHas('jabatan', fn ($q) => $q->where('nama_jabatan', 'IT'))
            ->get();

        foreach ($itUsers as $user) {
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id'         => $user->id,
                'is_read'         => false,
            ]);

            broadcast(new NotificationCreated($notification, $user->id));

            $unreadCount = NotificationRecipient::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            broadcast(new NotificationUnreadCountUpdated(
                $user->id,
                $unreadCount
            ));
        }
    }
}
