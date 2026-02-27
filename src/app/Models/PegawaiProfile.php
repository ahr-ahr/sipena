<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'nama',
        'nip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mapel()
{
    return $this->belongsToMany(
        Mapel::class,
        'kelas_mapel',
        'guru_id',
        'mapel_id'
    )->withPivot('kelas_id')
     ->withTimestamps();
}

public function kelasMapel()
{
    return $this->belongsToMany(
        Kelas::class,
        'kelas_mapel',
        'guru_id',
        'kelas_id'
    )->withPivot('mapel_id')
     ->withTimestamps();
}

}

?>