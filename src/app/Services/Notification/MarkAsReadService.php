<?php

namespace App\Services\Notification;

use App\Models\NotificationRecipient;
use App\Events\NotificationUnreadCountUpdated;
use App\Support\NotificationCounter;
use Illuminate\Support\Facades\DB;

class MarkAsReadService
{
    public function execute(NotificationRecipient $recipient): NotificationRecipient
    {
        return DB::transaction(function () use ($recipient) {

            if ($recipient->is_read) {
                return $recipient;
            }

            $recipient->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            $count = NotificationCounter::unreadCount($recipient->user_id);

            broadcast(new NotificationUnreadCountUpdated(
                $recipient->user_id,
                $count
            ));

            return $recipient->refresh();
        });
    }
}
