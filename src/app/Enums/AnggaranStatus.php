<?php

namespace App\Enums;

enum AnggaranStatus: string
{
    case DRAFT = 'draft';
    case DIAJUKAN_KE_TU = 'diajukan_ke_tu';
    case DITOLAK_TU = 'ditolak_tu';
    case DITERIMA_TU = 'diterima_tu';
    case DITOLAK_KEPSEK = 'ditolak_kepsek';
    case DISETUJUI_KEPSEK = 'disetujui_kepsek';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN_KE_TU => 'Diajukan ke TU',
            self::DITOLAK_TU => 'Ditolak TU',
            self::DITERIMA_TU => 'Diterima TU',
            self::DITOLAK_KEPSEK => 'Ditolak Kepala Sekolah',
            self::DISETUJUI_KEPSEK => 'Disetujui Kepala Sekolah',
        };
    }
}
