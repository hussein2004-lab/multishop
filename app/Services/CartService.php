<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Panier stocké en session : [product_id => quantité].
 */
class CartService
{
    private const KEY = 'cart';

    /**
     * Contenu détaillé du panier.
     *
     * @return Collection<int, array{product: Product, qty: int, line: int}>
     */
    public static function items(): Collection
    {
        $cart = session(self::KEY, []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::visible()
            ->with(['shop', 'category'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect();

        foreach ($cart as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product) {
                continue; // produit retiré ou modéré entre-temps
            }

            $qty = min($qty, max($product->stock, 1));

            $items->push([
                'product' => $product,
                'qty'     => $qty,
                'line'    => $product->price * $qty,
            ]);
        }

        return $items;
    }

    public static function add(Product $product, int $qty = 1): void
    {
        $cart = session(self::KEY, []);
        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $qty, $product->stock);
        session([self::KEY => $cart]);
    }

    public static function update(Product $product, int $qty): void
    {
        $cart = session(self::KEY, []);

        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = min($qty, $product->stock);
        }

        session([self::KEY => $cart]);
    }

    public static function remove(int $productId): void
    {
        $cart = session(self::KEY, []);
        unset($cart[$productId]);
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** Nombre total d'articles (pour le badge de la barre de navigation). */
    public static function count(): int
    {
        return (int) array_sum(session(self::KEY, []));
    }
}
