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
