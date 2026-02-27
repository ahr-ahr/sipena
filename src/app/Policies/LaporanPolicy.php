<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Laporan;
use App\Enums\LaporanStatus;
use App\Enums\UserType;

class LaporanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Laporan $laporan): bool
    {
        if ($user->id === $laporan->pelapor_id) {
            return true;
        }

        if (
            $user->hasJabatan('wali_kelas') &&
            $user->id === $laporan->wali_kelas_id
        ) {
            return true;
        }

        if (
            $user->hasJabatan('sarpras') &&
            in_array($laporan->status, [
                LaporanStatus::DITERIMA_WALI,
                LaporanStatus::DIPROSES_SARPRAS,
                LaporanStatus::SELESAI,
            ])
        ) {
            return true;
        }

        return false;
    }

public function update(User $user, Laporan $laporan): bool
{
    return
        $user->id === $laporan->pelapor_id &&
        $laporan->status === LaporanStatus::MENUNGGU;
}

public function viewHistory(User $user): bool
{
    return true;
}

    /**
     * Siswa membuat laporan
     */
    public function create(User $user): bool
    {
        return $user->tipe_user === UserType::SISWA;
    }

    /**
     * Wali Kelas menerima laporan
     */
    public function approveByWali(User $user, Laporan $laporan): bool
    {
        return
            $user->hasJabatan('wali_kelas')
            && $laporan->status === LaporanStatus::MENUNGGU;
    }

    /**
     * Wali Kelas menolak laporan
     */
    public function rejectByWali(User $user, Laporan $laporan): bool
    {
        return
            $user->hasJabatan('wali_kelas')
            && $laporan->status === LaporanStatus::MENUNGGU;
    }

    /**
     * Sarpras memproses laporan
     */
    public function processBySarpras(User $user, Laporan $laporan): bool
    {
        return
            $user->hasJabatan('sarpras')
            && $laporan->status === LaporanStatus::DITERIMA_WALI;
    }

    /**
     * Menyelesaikan laporan
     */
    public function finish(User $user, Laporan $laporan): bool
    {
        return
            $user->hasJabatan('sarpras')
            && $laporan->status === LaporanStatus::DIPROSES_SARPRAS;
    }
}
