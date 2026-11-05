<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic (assistant IA MultiShop)
    |--------------------------------------------------------------------------
    | Si la clé est renseignée, l'assistant IA utilise l'API Claude pour
    | comprendre les demandes des clients. Sinon, le moteur local est utilisé.
    */
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS / OTP
    |--------------------------------------------------------------------------
    | Driver "demo" : le code est affiché à l'écran (développement local).
    | Driver "log"  : le code est écrit dans storage/logs/laravel.log.
    | Pour la production, brancher un fournisseur SMS (Orange, MTN, Twilio...).
    */
    'sms' => [
        'driver' => env('SMS_DRIVER', 'demo'),
    ],

];
