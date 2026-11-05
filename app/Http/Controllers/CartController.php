<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = CartService::items();

        $byShop      = $items->groupBy(fn ($i) => $i['product']->shop_id);
        $subtotal    = $items->sum('line');
        $deliveryFee = (int) setting('delivery_fee', 1000) * max($byShop->count(), 0);

        return view('cart.index', [
            'items'       => $items,
            'byShop'      => $byShop,
            'subtotal'    => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total'       => $subtotal + $deliveryFee,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active && $product->is_visible && $product->shop->isApproved(), 404);

        $qty = max(1, (int) $request->input('qty', 1));

        if ($product->stock < 1) {
            return back()->with('error', __('Ce produit est en rupture de stock.'));
        }

        CartService::add($product, $qty);

        return back()->with('success', __('":name" ajouté au panier.', ['name' => $product->name]));
    }

    public function update(Request $request, Product $product)
    {
        $qty = (int) $request->input('qty', 1);

        CartService::update($product, $qty);

        return redirect()->route('cart.index');
    }

    public function remove(int $productId)
    {
        CartService::remove($productId);

        return redirect()->route('cart.index')->with('success', __('Article retiré du panier.'));
    }
}
