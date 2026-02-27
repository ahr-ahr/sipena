<?php

namespace App\Models;

class AppInstaller extends BaseModel
{
    protected $fillable = [
        'uuid',
        'installed_by',
        'environment',
        'db_version',
        'notes',
    ];
}
