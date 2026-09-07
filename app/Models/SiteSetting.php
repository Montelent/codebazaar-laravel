<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Alias used by HomeController and seeders.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::getValue($key, $default);
    }

    /**
     * Alias used by seeders / older call sites.
     */
    public static function set(string $key, mixed $value, ?string $group = null): void
    {
        static::setValue($key, $value, $group);
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $row = static::query()->where('key', $key)->first();
            if (! $row) {
                return $default;
            }
            return $row->value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function setValue(string $key, mixed $value, ?string $group = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        Cache::forget('site_setting_'.$key);
    }
}
