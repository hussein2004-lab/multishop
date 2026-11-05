<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Tableau de bord du vendeur. */
    public function index(Request $request)
    {
        $shop = $request->user()->shop;

        $stats = [
            'products'  => $shop->products()->count(),
            'orders'    => $shop->orders()->count(),
            'pending'   => $shop->orders()->where('status', 'pending')->count(),
            'delivered' => $shop->orders()->where('status', 'delivered')->count(),
            'revenue'   => $shop->orders()->where('payment_status', 'paid')->sum('total'),
            'rating'    => $shop->rating_avg,
        ];

        $recentOrders = $shop->orders()->with(['user', 'items'])->latest()->take(6)->get();
        $lowStock     = $shop->products()->where('stock', '<=', 3)->orderBy('stock')->take(5)->get();

        return view('seller.dashboard', compact('shop', 'stats', 'recentOrders', 'lowStock'));
    }

    /** Page d'attente : boutique pas encore validée par l'administrateur. */
    public function pending(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isSeller(), 403);

        $shop = $user->shop;

        if ($shop && $shop->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.pending', compact('shop'));
    }
}
