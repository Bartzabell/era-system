<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsSetting extends Model
{
    protected $table = 'terms_settings';

    protected $fillable = ['key', 'value'];

    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
