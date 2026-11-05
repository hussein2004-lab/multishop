<?php

use App\Models\Setting;

if (! function_exists('fcfa')) {
    /** Formate un montant en francs CFA : 100000 → "100 000 FCFA" */
    function fcfa(int|float|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 0, ',', ' ').' FCFA';
    }
}

if (! function_exists('setting')) {
    /** Lit un paramètre du site (sans erreur si la base n'est pas encore migrée). */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('current_theme')) {
    /** Thème courant : utilisateur connecté > session > clair par défaut. */
    function current_theme(): string
    {
        $theme = auth()->user()?->theme ?? session('theme', 'light');

        return in_array($theme, config('app.available_themes', ['light'])) ? $theme : 'light';
    }
}

if (! function_exists('locale_dir')) {
    /** Direction d'écriture de la langue courante : ltr ou rtl (arabe). */
    function locale_dir(): string
    {
        return config('app.available_locales.'.app()->getLocale().'.dir', 'ltr');
    }
}
