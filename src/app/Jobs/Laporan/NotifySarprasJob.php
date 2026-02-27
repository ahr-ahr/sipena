<?php

namespace App\Jobs\Laporan;

use App\Models\Laporan;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\NotificationCreated;
use App\Support\NotificationDebouncer;
use App\Support\NotificationCounter;
use App\Events\NotificationUnreadCountUpdated;

class NotifySarprasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Laporan $laporan
    ) {}

    public function handle(): void
    {
        $sarprasUsers = User::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'Sarpras');
        })->get();

        if ($sarprasUsers->isEmpty()) {
            return;
        }

        $notification = Notification::create([
            'laporan_id' => $this->laporan->id,
            'tipe'       => 'laporan_diterima_wali',
            'judul'      => 'Laporan Baru Siap Diproses',
            'pesan'      => "Laporan {$this->laporan->kode_laporan} telah diterima oleh wali kelas",
        ]);

        foreach ($sarprasUsers as $user) {
            $debounceKey = NotificationDebouncer::key(
                'laporan_diterima_wali',
                $this->laporan->id,
                $user->id
            );

            if (! NotificationDebouncer::allow($debounceKey, 15)) {
                continue;
            }

            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id'         => $user->id,
            ]);

            broadcast(new NotificationCreated(
                $notification->load('laporan'),
                $user->id
            ));

            $count = NotificationCounter::unreadCount($user->id);

            broadcast(new NotificationUnreadCountUpdated(
                $user->id,
                $count
            ));
        }
    }
}