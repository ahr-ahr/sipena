<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComment;
use Illuminate\Support\Facades\DB;

class AddTicketCommentService
{
    public function execute(
        Ticket $ticket,
        User $user,
        string $message,
        bool $isInternal = false
    ): TicketComment {
        return DB::transaction(function () use ($ticket, $user, $message, $isInternal) {

            $isIT = $user->jabatan
                ->pluck('nama_jabatan')
                ->contains('IT');

            // Jika bukan IT
            if (! $isIT) {
                if (! $ticket->hasITReply()) {
                    throw new \DomainException(
                        'Anda belum bisa membalas tiket sebelum IT merespon.'
                    );
                }
            }

            return TicketComment::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $user->id,
                'message'     => $message,
                'is_internal' => $isInternal,
            ]);
        });
    }
}
