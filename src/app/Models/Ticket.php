<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany
};

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to',
        'priority',
        'status',
        'title',
        'description',
        'closed_at',
        'laporan_id',
        'external_vendor',
        'external_notes',
        'started_at',
        'resolved_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function hasITReply(): bool
{
    return $this->comments()
        ->whereHas('user.jabatan', function ($q) {
            $q->where('nama_jabatan', 'IT');
        })
        ->exists();
}

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TicketStatusLog::class);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function laporan(): BelongsTo
{
    return $this->belongsTo(Laporan::class, 'laporan_id');
}
}
