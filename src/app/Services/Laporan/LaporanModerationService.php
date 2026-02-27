<?php

namespace App\Services\Laporan;

class LaporanModerationService
{
    public function isBlocked(string $text): bool
    {
        $blockedWords = [
            'kontol',
            'memek',
            'ngentot',
            'fuck',
            'porn',
            'bokep',
        ];

        foreach ($blockedWords as $word) {
            if (stripos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}
