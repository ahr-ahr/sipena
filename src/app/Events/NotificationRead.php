<?php

namespace App\Events;

use App\Models\NotificationRecipient;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationRead implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public NotificationRecipient $recipient
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->recipient->user_id);
    }

    public function broadcastAs(): string
    {
        return 'notification.read';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->recipient->notification->uuid,
            'read_at' => $this->recipient->read_at?->toISOString(),
        ];
    }
}
