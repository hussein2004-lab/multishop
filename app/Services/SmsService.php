<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Envoi de SMS (codes OTP de paiement).
 *
 * Driver "demo" : le code reste consultable à l'écran (développement local).
 * Driver "log"  : le code est uniquement écrit dans storage/logs/laravel.log.
 *
 * Pour la production : brancher ici un vrai fournisseur SMS
 * (Orange SMS API, MTN, Twilio, Vonage...) dans la méthode send().
 */
class SmsService
{
    public static function send(string $phone, string $message): void
    {
        // Toujours tracé dans les logs pour le développement
        Log::info('[SMS] → '.$phone.' : '.$message);

        // Production : appeler ici l'API du fournisseur SMS choisi.
        // Exemple :
        // Http::post('https://api.fournisseur-sms.ci/send', [
        //     'to' => $phone, 'message' => $message, 'api_key' => config('services.sms.key'),
        // ]);
    }

    public static function isDemoMode(): bool
    {
        return config('services.sms.driver', 'demo') === 'demo';
    }
}
