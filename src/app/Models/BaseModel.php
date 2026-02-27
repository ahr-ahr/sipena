<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

abstract class BaseModel extends Model
{
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'uuid')) {
                $model->uuid ??= (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return Schema::hasColumn($this->getTable(), 'uuid') ? 'uuid' : parent::getRouteKeyName();
    }
}

?>