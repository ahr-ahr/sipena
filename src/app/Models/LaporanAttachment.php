<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class LaporanAttachment extends Model
{

protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'laporan_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function getUrlAttribute()
{
    return rtrim(env('MINIO_PUBLIC_URL'), '/') . '/sipena/' . $this->file_path;
}

protected $appends = ['url'];
}
