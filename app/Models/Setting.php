<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Per-request in-memory cache of all settings (key => value). */
    protected static ?array $bag = null;

    public static function get(string $key, $default = null)
    {
        if (static::$bag === null) {
            static::$bag = static::pluck('value', 'key')->all();
        }

        return static::$bag[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        if (static::$bag !== null) {
            static::$bag[$key] = $value;
        }
    }
}
