<?php

namespace App\Models;

use App\Services\KodeGeneratorService;

class Anggaran extends BaseModel
{
    protected $table = 'anggaran';
    protected $fillable = [
        'uuid',
        'kode_anggaran',
        'laporan_id',
        'dibuat_oleh',
        'total_biaya',
        'status',
        'catatan',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($anggaran) {
            if (empty($anggaran->kode_anggaran)) {
                $anggaran->kode_anggaran = app(KodeGeneratorService::class)->generateAnggaran();
            }
        });
    }

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function details()
    {
        return $this->hasMany(AnggaranDetail::class);
    }
}
