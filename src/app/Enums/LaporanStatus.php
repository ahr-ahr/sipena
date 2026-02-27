<?php

namespace App\Enums;

enum LaporanStatus: string
{
    case MENUNGGU = 'menunggu';
    case DIPROSES = 'diproses';
    case DITOLAK = 'ditolak';
    case DISETUJUI = 'disetujui';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU => 'Menunggu',
            self::DIPROSES => 'Diproses',
            self::DITOLAK => 'Ditolak',
            self::DISETUJUI => 'Disetujui',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU => 'gray',
            self::DIPROSES => 'blue',
            self::DITOLAK => 'red',
            self::DISETUJUI => 'green',
            self::SELESAI => 'emerald',
        };
    }
}
