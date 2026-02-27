<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserType;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'tipe_user'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tipe_user' => UserType::class,
        ];
    }

    public function waliKelas()
    {
        return $this->belongsToMany(
            Kelas::class,
            'kelas_wali',
            'user_id',
            'kelas_id'
        );
    }

    public function siswaProfile()
    {
        return $this->hasOne(SiswaProfile::class);
    }

    public function pegawaiProfile()
    {
        return $this->hasOne(PegawaiProfile::class);
    }

    public function jabatan()
    {
        return $this->belongsToMany(Jabatan::class, 'user_jabatan');
    }

    public function laporanDibuat()
    {
        return $this->hasMany(LaporanDibuat::class, 'pelapor_id');
    }

    public function laporanSebagaiWali()
    {
        return $this->hasMany(Laporan::class, 'wali_kelas_id');
    }

    public function anggaranDibuat()
    {
        return $this->hasMany(Anggaran::class, 'dibuat_oleh');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function ticketAttachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'uploaded_by');
    }

    public function hasAnyJabatan(array $names): bool
{
    return $this->jabatan()
        ->whereIn('nama_jabatan', $names)
        ->exists();
}

public function hasJabatan(string|array $names): bool
{
    $names = (array) $names;

    return $this->jabatan()
        ->whereIn('nama_jabatan', $names)
        ->exists();
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

    protected static function booted()
{
    static::creating(function ($model) {
        if (empty($model->uuid)) {
            $model->uuid = (string) Str::uuid();
        }
    });
}
}
