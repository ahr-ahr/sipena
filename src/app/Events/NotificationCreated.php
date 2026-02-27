<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Notification $notification,
        public int $userId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->notification->uuid,
            'judul'      => $this->notification->judul,
            'pesan'      => $this->notification->pesan,
            'tipe'       => $this->notification->tipe,
            'source'     => $this->resolveSource(),
            'created_at' => $this->notification->created_at?->toISOString(),
        ];
    }

    /**
     * Tentukan sumber notifikasi (laporan / ticket)
     */
    protected function resolveSource(): ?array
    {
        if ($this->notification->laporan_id && $this->notification->laporan) {
            return [
                'type' => 'laporan',
                'id'   => $this->notification->laporan_id,
                'kode' => $this->notification->laporan->kode_laporan,
            ];
        }

        if ($this->notification->ticket_id && $this->notification->ticket) {
            return [
                'type' => 'ticket',
                'id'   => $this->notification->ticket_id,
                'kode' => $this->notification->ticket->ticket_number,
            ];
        }

        return null;
    }
}
