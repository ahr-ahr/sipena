<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Ambil value setting (pakai cache)
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever(
            "system_setting:{$key}",
            fn () => static::where('key', $key)->value('value') ?? $default
        );
    }

    /**
     * Simpan / update setting (auto clear cache)
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("system_setting:{$key}");
    }

    /**
     * Ambil semua setting berdasarkan group
     */
    public static function group(string $group)
    {
        return static::where('group', $group)
            ->pluck('value', 'key');
    }

    /**
     * Clear semua cache setting (opsional)
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
