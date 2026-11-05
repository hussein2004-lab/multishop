<?php

return [

    'name' => env('APP_NAME', 'MultiShop'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => env('APP_TIMEZONE', 'Africa/Abidjan'),

    'locale' => env('APP_LOCALE', 'fr'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'fr'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'fr_FR'),

    /*
    |--------------------------------------------------------------------------
    | Langues disponibles sur MultiShop
    |--------------------------------------------------------------------------
    */
    'available_locales' => [
        'fr' => ['name' => 'Français',  'native' => 'Français',  'flag' => '🇫🇷', 'dir' => 'ltr'],
        'en' => ['name' => 'English',   'native' => 'English',   'flag' => '🇬🇧', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic',    'native' => 'العربية',   'flag' => '🇸🇦', 'dir' => 'rtl'],
        'es' => ['name' => 'Spanish',   'native' => 'Español',   'flag' => '🇪🇸', 'dir' => 'ltr'],
        'de' => ['name' => 'German',    'native' => 'Deutsch',   'flag' => '🇩🇪', 'dir' => 'ltr'],
        'ja' => ['name' => 'Japanese',  'native' => '日本語',     'flag' => '🇯🇵', 'dir' => 'ltr'],
        'zh' => ['name' => 'Chinese',   'native' => '中文',       'flag' => '🇨🇳', 'dir' => 'ltr'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thèmes disponibles
    |--------------------------------------------------------------------------
    */
    'available_themes' => ['light', 'dark', 'sepia', 'gray'],

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
