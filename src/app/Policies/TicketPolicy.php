<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserType;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    // public function view(User $user, Ticket $ticket): bool
    // {
    //     // pemilik tiket
    //     if ($user->id === $ticket->user_id) {
    //         return true;
    //     }

    //     // IT
    //     if (
    //         $user->tipe_user === UserType::PEGAWAI &&
    //         $user->hasJabatan('IT')
    //     ) {
    //         return true;
    //     }

    //     return false;
    // }

    // public function update(User $user, Ticket $ticket): bool
    // {
    //     return
    //         $user->id === $ticket->user_id &&
    //         $ticket->status === 'open';
    // }

     public function viewHistory(User $user): bool
    {
        return true;
    }
}
