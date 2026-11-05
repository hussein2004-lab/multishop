<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /** Modération des produits. */
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q'));
        $filter = $request->query('filtre');   // hidden = produits masqués

        $products = Product::with(['shop', 'category'])
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($filter === 'hidden', fn ($query) => $query->where('is_visible', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products', compact('products', 'q', 'filter'));
    }

    /** Masquer / réafficher un produit (modération). */
    public function toggleVisible(Product $product)
    {
        $product->update(['is_visible' => ! $product->is_visible]);

        return back()->with('success', $product->is_visible
            ? __('Produit ":name" réaffiché.', ['name' => $product->name])
            : __('Produit ":name" masqué.', ['name' => $product->name]));
    }

    /** Suppression définitive. */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', __('Produit supprimé définitivement.'));
    }
}
