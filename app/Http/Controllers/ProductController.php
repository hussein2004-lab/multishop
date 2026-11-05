<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** Catalogue avec recherche, filtres et tri (dont tendances 🔥). */
    public function index(Request $request)
    {
        $q        = trim((string) $request->query('q'));
        $catSlug  = $request->query('categorie');
        $priceMin = $request->query('prix_min');
        $priceMax = $request->query('prix_max');
        $sort     = $request->query('tri', 'tendance');   // tendance | recent | prix_asc | prix_desc

        $products = Product::visible()
            ->with(['shop', 'category'])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")))
            ->when($catSlug, fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $catSlug)))
            ->when(is_numeric($priceMin), fn ($query) => $query->where('price', '>=', (int) $priceMin))
            ->when(is_numeric($priceMax), fn ($query) => $query->where('price', '<=', (int) $priceMax));

        $products = match ($sort) {
            'recent'    => $products->latest(),
            'prix_asc'  => $products->orderBy('price'),
            'prix_desc' => $products->orderByDesc('price'),
            default     => $products->trending(),
        };

        $products = $products->paginate(12)->withQueryString();

        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'q', 'catSlug', 'priceMin', 'priceMax', 'sort'));
    }

    /** Fiche produit. */
    public function show(Request $request, Product $product)
    {
        $isOwnerOrAdmin = $request->user()
            && ($request->user()->isAdmin() || $request->user()->id === $product->shop->user_id);

        $visible = $product->is_active && $product->is_visible && $product->shop->isApproved();

        abort_unless($visible || $isOwnerOrAdmin, 404);

        // Tendances + adaptation aux préférences du client
        if ($visible) {
            $product->recordView($request->user(), $request->session()->getId());
        }

        $similar = Product::visible()->inStock()
            ->with(['shop', 'category'])
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->trending()
            ->take(4)
            ->get();

        $reviews = $product->shop->reviews()->with('user')->take(4)->get();

        return view('products.show', compact('product', 'similar', 'reviews'));
    }
}
