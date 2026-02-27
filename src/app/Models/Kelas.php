<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama',
    ];

    /**
     * Siswa dalam kelas ini
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(SiswaProfile::class);
    }

    /**
     * Wali kelas aktif (1 kelas = 1 wali)
     */
    public function wali(): HasOne
    {
        return $this->hasOne(KelasWali::class);
    }

    /**
     * Shortcut: user wali kelas
     */
    public function waliUser()
    {
        return $this->belongsToMany(
            User::class,
            'kelas_wali',
            'kelas_id',
            'user_id'
        );
    }

    public function mapel()
{
    return $this->belongsToMany(
        Mapel::class,
        'kelas_mapel'
    )->withPivot('guru_id')
     ->withTimestamps();
}

public function guruMapel()
{
    return $this->belongsToMany(
        User::class,
        'kelas_mapel',
        'kelas_id',
        'guru_id'
    )->withPivot('mapel_id')
     ->withTimestamps();
}

}
