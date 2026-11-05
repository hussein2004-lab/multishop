<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSeller() || ! $user->is_active) {
            abort(403);
        }

        // Boutique non encore validée par l'administrateur
        $shop = $user->shop;

        if ((! $shop || ! $shop->isApproved()) && ! $request->routeIs('seller.pending')) {
            return redirect()->route('seller.pending');
        }

        return $next($request);
    }
}
