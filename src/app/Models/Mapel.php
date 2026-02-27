<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $table = 'mapel';

    protected $fillable = [
        'nama',
        'kode',
    ];

    public function kelas()
    {
        return $this->belongsToMany(
            Kelas::class,
            'kelas_mapel'
        )->withPivot('guru_id')
         ->withTimestamps();
    }

    public function guru()
    {
        return $this->belongsToMany(
            User::class,
            'kelas_mapel',
            'mapel_id',
            'guru_id'
        )->withPivot('kelas_id')
         ->withTimestamps();
    }
}

?>