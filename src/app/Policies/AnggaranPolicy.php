<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Anggaran;
use App\Enums\AnggaranStatus;

class AnggaranPolicy
{
    /**
     * TU menerima anggaran
     */
    public function approveByTU(User $user, Anggaran $anggaran): bool
    {
        return
            $user->hasJabatan('tu')
            && $anggaran->status === AnggaranStatus::DIAJUKAN_KE_TU;
    }

    /**
     * TU menolak anggaran
     */
    public function rejectByTU(User $user, Anggaran $anggaran): bool
    {
        return
            $user->hasJabatan('tu')
            && $anggaran->status === AnggaranStatus::DIAJUKAN_KE_TU;
    }

    /**
     * Kepala Sekolah menerima anggaran
     */
    public function approveByKepsek(User $user, Anggaran $anggaran): bool
    {
        return
            $user->hasJabatan('kepala_sekolah')
            && $anggaran->status === AnggaranStatus::DITERIMA_TU;
    }

    /**
     * Kepala Sekolah menolak anggaran
     */
    public function rejectByKepsek(User $user, Anggaran $anggaran): bool
    {
        return
            $user->hasJabatan('kepala_sekolah')
            && $anggaran->status === AnggaranStatus::DITERIMA_TU;
    }
}
