<?php

namespace App\Services\Laporan;

use App\Models\Laporan;
use App\Models\User;
use App\Models\AuditLog;
use App\Enums\LaporanStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Events\NotificationCreated;

class RejectLaporanByWaliService
{
    public function execute(
        Laporan $laporan,
        User $wali
    ): Laporan {
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
                'status' => LaporanStatus::DITOLAK_WALI,
            ]);

            AuditLog::create([
                'user_id'     => $wali->id,
                'action'      => 'reject_laporan_wali',
                'target_type' => 'laporan',
                'target_id'   => $laporan->id,
                'description' => "Laporan {$laporan->kode_laporan} ditolak oleh wali kelas.",
                'ip_address'  => request()->ip(),
            ]);

            $notification = Notification::create([
                'judul'      => 'Laporan Ditolak Wali Kelas',
                'pesan'      => "Laporan {$laporan->kode_laporan} ditolak oleh wali kelas.",
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

            return $laporan->refresh();
        });
    }
}
