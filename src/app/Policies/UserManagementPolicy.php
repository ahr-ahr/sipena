<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserType;

class UserManagementPolicy
{
    protected function isIT(User $user): bool
    {
        if ($user->tipe_user !== UserType::PEGAWAI) {
            return false;
        }

        return $user->jabatan
            ->pluck('nama_jabatan')
            ->map(fn ($j) => strtolower($j))
            ->contains('it');
    }

    public function viewAny(User $user): bool
    {
        return $this->isIT($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isIT($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isIT($user);
    }
}
