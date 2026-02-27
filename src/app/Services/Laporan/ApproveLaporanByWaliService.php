<?php

namespace App\Services\Laporan;

use App\Models\Laporan;
use App\Models\User;
use App\Models\AuditLog;
use App\Enums\LaporanStatus;
use Illuminate\Support\Facades\DB;
use App\Jobs\Laporan\NotifySarprasJob;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Events\NotificationCreated;
use Illuminate\Support\Str;

class ApproveLaporanByWaliService
{
    public function execute(Laporan $laporan, User $wali): Laporan
    {
        return DB::transaction(function () use ($laporan, $wali) {

            if ($laporan->status !== LaporanStatus::MENUNGGU) {
                throw new \DomainException('Laporan tidak dalam status menunggu');
            }

            $kelasWaliId = $laporan->pelapor
                ->siswaProfile
                ?->kelas
                ?->waliUser
                ?->pluck('id')
                ->first();

            if ($kelasWaliId !== $wali->id) {
                throw new \DomainException('Anda bukan wali kelas dari siswa ini');
            }

            $laporan->update([
                'status' => LaporanStatus::DITERIMA_WALI,
            ]);

            AuditLog::create([
                'user_id'     => $wali->id,
                'action'      => 'approve_laporan_wali',
                'target_type' => 'laporan',
                'target_id'   => $laporan->id,
                'description' => "Laporan {$laporan->kode_laporan} diterima oleh wali kelas",
                'ip_address'  => request()->ip(),
            ]);

            $notification = Notification::create([
                'judul'      => 'Laporan Disetujui Wali Kelas',
                'pesan'      => "Laporan {$laporan->kode_laporan} telah disetujui oleh wali kelas",
                'tipe'       => 'laporan',
                'laporan_id' => $laporan->id,
            ]);

            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id'         => $laporan->pelapor_id,
                'is_read'         => false,
            ]);

            event(new NotificationCreated(
                $notification,
                $laporan->pelapor_id
            ));

            dispatch(new NotifySarprasJob($laporan));

            return $laporan->refresh();
        });
    }
}
