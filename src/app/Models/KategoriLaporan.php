<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriLaporan extends Model
{
    public $timestamps = false;

    protected $table = 'kategori_laporan';

    protected $fillable = [
        'nama'
    ];

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }
}

?>