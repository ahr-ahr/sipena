<?php

namespace App\Support;

use App\Enums\LaporanRole;

class JabatanMap
{
    public const MAP = [
        'bk' => 'BK',
        'sarpras' => 'Sarpras',
        'wali' => 'Wali Kelas',
        'kesiswaan' => 'Kesiswaan',
        'tu' => 'TU',
        'kepsek' => 'Kepala Sekolah',
        'guru' => 'Guru',
    ];

    public static function getName(LaporanRole $role): string
    {
        return self::MAP[$role->value] ?? '';
    }
}
