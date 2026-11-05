<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Compte désactivé par l'administrateur → déconnexion immédiate
        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', __('Votre compte a été désactivé.'));
        }

        // Mode maintenance : seuls les administrateurs peuvent naviguer
        $maintenanceOn = setting('maintenance', '0') === '1';

        if ($maintenanceOn && ! ($user && $user->isAdmin()) && ! $request->routeIs('login', 'login.post', 'logout')) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
