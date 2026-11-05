<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Lit un paramètre du site (avec cache). */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember('site_settings', 300, function () {
            return static::pluck('value', 'key')->all();
        });

        return $all[$key] ?? $default;
    }

    /** Écrit un paramètre du site et vide le cache. */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings');
    }
}
