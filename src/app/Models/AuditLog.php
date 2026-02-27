<?php

namespace App\Models;

class AuditLog extends BaseModel
{
    protected $fillable = [
        'uuid',
        'user_id',
        'action',
        'target_type',
        'target_id',
        'description',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
