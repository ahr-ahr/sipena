<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Enums\UserType;
use App\Models\Laporan;
use App\Models\Anggaran;
use App\Policies\LaporanPolicy;
use App\Policies\AnggaranPolicy;
use App\Policies\NotificationRecipientPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Broadcast::routes();
        require base_path('routes/channels.php');

        Gate::define('create-ticket', function ($user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'Sarpras');
        });

        Gate::define('manage-tickets', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->hasAnyJabatan(['IT', 'Sarpras']);
        });

        Gate::define('kepsek-laporan', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'Kepala Sekolah');
        });

        Gate::define('kesiswaan-laporan', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'Kesiswaan');
        });

        Gate::define('bk-laporan', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'BK');
        });

        Gate::define('tu-laporan', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'TU');
        });

        Gate::define('sarpras-laporan', function (User $user) {
            if ($user->tipe_user !== \App\Enums\UserType::PEGAWAI) {
                return false;
            }

            return $user->jabatan
                ->pluck('nama_jabatan')
                ->map(fn ($j) => strtolower($j))
                ->contains('sarpras');
        });

        // Audit Log Policies
        Gate::define('view-audit-log', function (User $user) {
            if ($user->tipe_user !== UserType::PEGAWAI) {
                return false;
            }

            return $user->jabatan
                ->pluck('nama_jabatan')
                ->map(fn ($j) => strtolower($j))
                ->contains('it');
        });

        // System Settings Policies
        Gate::define('manage-settings', function (User $user) {
            if ($user->tipe_user !== \App\Enums\UserType::PEGAWAI) {
                return false;
            }

            return $user->jabatan
                ->pluck('nama_jabatan')
                ->map(fn ($j) => strtolower($j))
                ->contains('it');
        });

        // Wali Kelas Policies
        Gate::define('wali-laporan', function (User $user) {
            return $user->tipe_user === \App\Enums\UserType::PEGAWAI
                && $user->jabatan->contains('nama_jabatan', 'Wali Kelas');
        });

        // User Management Policies
        Gate::define('manage-users', function (User $user) {
            if ($user->tipe_user !== UserType::PEGAWAI) {
                return false;
            }

            return $user->jabatan
                ->pluck('nama_jabatan')
                ->map(fn ($j) => strtolower($j))
                ->contains('it');
        });

        // Notification Recipient Policies
        Gate::define('notification.view', [NotificationRecipientPolicy::class, 'view']);
        Gate::define('notification.read', [NotificationRecipientPolicy::class, 'markAsRead']);
        Gate::define('notification.delete', [NotificationRecipientPolicy::class, 'delete']);

        // Laporan Policies
        Gate::define('laporan.approve.wali', [LaporanPolicy::class, 'approveByWali']);
        Gate::define('laporan.reject.wali', [LaporanPolicy::class, 'rejectByWali']);
        Gate::define('laporan.process.sarpras', [LaporanPolicy::class, 'processBySarpras']);
        Gate::define('laporan.finish', [LaporanPolicy::class, 'finish']);

        // Anggaran Policies
        Gate::define('anggaran.approve.tu', [AnggaranPolicy::class, 'approveByTU']);
        Gate::define('anggaran.reject.tu', [AnggaranPolicy::class, 'rejectByTU']);
        Gate::define('anggaran.approve.kepsek', [AnggaranPolicy::class, 'approveByKepsek']);
        Gate::define('anggaran.reject.kepsek', [AnggaranPolicy::class, 'rejectByKepsek']);

        // Ticket Policies
        
    }
}
