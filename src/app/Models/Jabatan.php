<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    public $timestamps = false;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_jabatan');
    }
}

?>