<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranDetail extends Model
{
        protected $table = 'anggaran_detail';

    protected $fillable = [
        'anggaran_id',
        'nama_item',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class);
    }
}
