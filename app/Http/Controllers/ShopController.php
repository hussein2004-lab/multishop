<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /** Liste des boutiques avec filtre de fiabilité. */
    public function index(Request $request)
    {
        $q           = trim((string) $request->query('q'));
        $reliability = $request->query('fiabilite', 'all');   // all | 90 | 75 | 50 | new
        $sort        = $request->query('tri', 'fiabilite');   // fiabilite | note | recent

        $shops = Shop::approved()
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)->where('is_visible', true)])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%"));
            })
            ->get();

        // Filtre sur le score de fiabilité (calculé)
        $shops = match ($reliability) {
            '90'    => $shops->filter(fn ($s) => ($s->reliability ?? 0) >= 90),
            '75'    => $shops->filter(fn ($s) => ($s->reliability ?? 0) >= 75),
            '50'    => $shops->filter(fn ($s) => ($s->reliability ?? 0) >= 50),
            'new'   => $shops->filter(fn ($s) => $s->reliability === null),
            default => $shops,
        };

        $shops = match ($sort) {
            'note'   => $shops->sortByDesc('rating_avg'),
            'recent' => $shops->sortByDesc('created_at'),
            default  => $shops->sortByDesc(fn ($s) => $s->reliability ?? -1),
        };

        return view('shops.index', [
            'shops'       => $shops->values(),
            'q'           => $q,
            'reliability' => $reliability,
            'sort'        => $sort,
        ]);
    }

    /** Vitrine d'une boutique. */
    public function show(Request $request, Shop $shop)
    {
        abort_unless($shop->isApproved(), 404);

        $categorySlug = $request->query('categorie');

        $products = $shop->products()
            ->where('is_active', true)
            ->where('is_visible', true)
            ->with('category')
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $shopCategories = $shop->products()
            ->where('is_active', true)
            ->where('is_visible', true)
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        $reviews = $shop->reviews()->with('user')->take(6)->get();

        return view('shops.show', compact('shop', 'products', 'shopCategories', 'categorySlug', 'reviews'));
    }
}
