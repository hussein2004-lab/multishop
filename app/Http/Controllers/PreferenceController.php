<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /** Change le thème (clair, sombre, sépia, gris clair). */
    public function theme(Request $request)
    {
        $data = $request->validate([
            'theme' => 'required|in:'.implode(',', config('app.available_themes')),
        ]);

        session(['theme' => $data['theme']]);

        $request->user()?->update(['theme' => $data['theme']]);

        return back();
    }

    /** Change la langue du site. */
    public function locale(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required|in:'.implode(',', array_keys(config('app.available_locales'))),
        ]);

        session(['locale' => $data['locale']]);

        $request->user()?->update(['locale' => $data['locale']]);

        return back();
    }
}
