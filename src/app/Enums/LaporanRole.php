<?php

namespace App\Enums;

enum LaporanRole: string
{
    case WALI = 'wali';
    case BK = 'bk';
    case KESISWAAN = 'kesiswaan';
    case SARPRAS = 'sarpras';
    case GURU = 'guru';
    case TU = 'tu';
    case KEPSEK = 'kepsek';

    public function label(): string
    {
        return match ($this) {
            self::WALI => 'Wali Kelas',
            self::BK => 'BK',
            self::KESISWAAN => 'Kesiswaan',
            self::SARPRAS => 'Sarpras',
            self::GURU => 'Guru',
            self::TU => 'Tata Usaha',
            self::KEPSEK => 'Kepala Sekolah',
        };
    }
}