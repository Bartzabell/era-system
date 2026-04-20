<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSetting extends Model
{
    protected $table = 'login_settings';

    protected $fillable = ['key', 'value'];

    /**
     * Get all settings as a key-value array.
     */
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Get a single setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set (upsert) a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
