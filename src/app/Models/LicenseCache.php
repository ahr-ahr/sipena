<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseCache extends Model
{
    protected $fillable = [
        'license_key',
        'status',
        'expires_at',
        'last_checked_at',
        'grace_until',
        'server_fingerprint',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'last_checked_at' => 'datetime',
        'grace_until' => 'date',
    ];
}
