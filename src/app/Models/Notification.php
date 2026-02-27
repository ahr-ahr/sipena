<?php

namespace App\Models;

class Notification extends BaseModel
{
    protected $fillable = [
        'uuid',
        'laporan_id',
        'ticket_id',
        'tipe',
        'judul',
        'pesan',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

     public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }
}
