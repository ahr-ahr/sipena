<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasWali extends Model
{
    protected $table = 'kelas_wali';

    protected $fillable = [
        'kelas_id',
        'user_id',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
