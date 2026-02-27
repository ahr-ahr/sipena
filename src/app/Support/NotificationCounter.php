<?php

namespace App\Support;

use App\Models\NotificationRecipient;

class NotificationCounter
{
    public static function unreadCount(int $userId): int
    {
        return NotificationRecipient::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}
