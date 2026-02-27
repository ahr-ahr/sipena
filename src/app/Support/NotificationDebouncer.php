<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class NotificationDebouncer
{
    /**
     * Cek apakah notif boleh dikirim
     *
     * @param string $key
     * @param int $ttlSeconds
     */
    public static function allow(string $key, int $ttlSeconds = 10): bool
    {
        /**
         * Cache::add:
         * - return TRUE kalau key BELUM ADA
         * - return FALSE kalau key SUDAH ADA
         * - atomic (aman untuk concurrent job)
         */
        return Cache::add($key, true, now()->addSeconds($ttlSeconds));
    }

    /**
     * Helper untuk bikin key konsisten
     */
    public static function key(
        string $type,
        int $laporanId,
        int $userId
    ): string {
        return "notif:{$type}:laporan:{$laporanId}:user:{$userId}";
    }
}
