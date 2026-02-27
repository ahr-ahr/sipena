<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NotificationRecipient;

class NotificationRecipientPolicy
{
    /**
     * Melihat notif (list / detail)
     */
    public function view(User $user, NotificationRecipient $recipient): bool
    {
        return $recipient->user_id === $user->id;
    }

    /**
     * Menandai notif sebagai dibaca
     */
    public function markAsRead(User $user, NotificationRecipient $recipient): bool
    {
        return $recipient->user_id === $user->id;
    }

    /**
     * Menghapus notif (opsional)
     */
    public function delete(User $user, NotificationRecipient $recipient): bool
    {
        return $recipient->user_id === $user->id;
    }
}
