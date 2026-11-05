<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /** Liste des boutiques (validation des inscriptions). */
    public function index(Request $request)
    {
        $status = $request->query('statut', 'pending');

        $shops = Shop::with('user')
            ->withCount('products')
            ->when(in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = Shop::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.shops', compact('shops', 'status', 'counts'));
    }

    /** ✅ Valider l'inscription d'un magasin. */
    public function approve(Shop $shop)
    {
        $shop->update(['status' => 'approved', 'rejection_reason' => null]);

        return back()->with('success', __('Boutique ":name" approuvée.', ['name' => $shop->name]));
    }

    /** ❌ Refuser l'inscription d'un magasin. */
    public function reject(Request $request, Shop $shop)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:500']);

        $shop->update([
            'status'           => 'rejected',
            'rejection_reason' => $data['reason'] ?? null,
        ]);

        return back()->with('success', __('Boutique ":name" refusée.', ['name' => $shop->name]));
    }
}
