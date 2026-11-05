<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Shop;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 🔥 Produits tendance
        $trending = Product::visible()->inStock()
            ->with(['shop', 'category'])
            ->trending()
            ->take(8)
            ->get();

        // ✨ "Pour vous" : le site s'adapte aux préférences de navigation
        $preferredCategoryIds = $this->preferredCategoryIds($request);

        $forYou = collect();
        if (! empty($preferredCategoryIds)) {
            $forYou = Product::visible()->inStock()
                ->with(['shop', 'category'])
                ->whereIn('category_id', $preferredCategoryIds)
                ->latest()
                ->take(4)
                ->get();
        }

        // Meilleures boutiques (fiabilité)
        $topShops = Shop::approved()
            ->withCount('products')
            ->orderByDesc('rating_avg')
            ->orderByDesc('delivered_count')
            ->take(4)
            ->get();

        // Catégories, les préférées du client en premier
        $categories = Category::withCount('products')->get()
            ->sortByDesc(fn (Category $c) => in_array($c->id, $preferredCategoryIds) ? 1 : 0)
            ->values();

        return view('home', compact('trending', 'forYou', 'topShops', 'categories'));
    }

    /** Catégories les plus consultées (utilisateur connecté ou session anonyme). */
    private function preferredCategoryIds(Request $request): array
    {
        $q = ProductView::query()
            ->whereNotNull('category_id')
            ->where('created_at', '>=', now()->subDays(30));

        if ($request->user()) {
            $q->where('user_id', $request->user()->id);
        } else {
            $q->where('session_id', $request->session()->getId());
        }

        try {
            return $q->selectRaw('category_id, COUNT(*) as nb')
                ->groupBy('category_id')
                ->orderByDesc('nb')
                ->limit(3)
                ->pluck('category_id')
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
