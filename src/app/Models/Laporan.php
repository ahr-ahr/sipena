<?php

namespace App\Models;

use App\Services\KodeGeneratorService;
use App\Enums\LaporanStatus;
use App\Enums\LaporanRole;

class Laporan extends BaseModel
{
    protected $table = 'laporan';

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'uuid',
        'kode_laporan',
        'judul',
        'deskripsi',
        'kategori_id',
        'pelapor_id',
        'wali_kelas_id',
        'mapel_id',
        'current_role',
        'status',
        'priority',
        'ai_label',
        'ai_score',
    ];

    protected $casts = [
        'status' => LaporanStatus::class,
        'current_role' => LaporanRole::class,
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($laporan) {
            if (empty($laporan->kode_laporan)) {
                $laporan->kode_laporan = app(KodeGeneratorService::class)->laporan();
            }
        });
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriLaporan::class);
    }

    public function attachments()
    {
        return $this->hasMany(LaporanAttachment::class);
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function anggaran()
    {
        return $this->hasOne(Anggaran::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function ticket()
{
    return $this->hasOne(Ticket::class, 'laporan_id');
}

public function mapel()
{
    return $this->belongsTo(Mapel::class);
}

}
