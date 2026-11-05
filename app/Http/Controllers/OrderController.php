<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Mes commandes (client). */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['shop', 'items'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /** Détail d'une commande : accessible au client, au vendeur concerné et aux admins. */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        $allowed = $user->id === $order->user_id
            || $user->isAdmin()
            || ($user->isSeller() && $user->shop && $user->shop->id === $order->shop_id);

        abort_unless($allowed, 403);

        $order->load(['shop', 'items.product', 'user', 'review']);

        return view('orders.show', compact('order'));
    }

    /** Annulation par le client (avant expédition). */
    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->canBeCancelled()) {
            return back()->with('error', __('Cette commande ne peut plus être annulée.'));
        }

        $order->markCancelled();

        return back()->with('success', __('Commande annulée.'));
    }

    /** Avis client après livraison → fiabilité du vendeur. */
    public function review(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'delivered' || $order->review) {
            return back()->with('error', __('Vous ne pouvez pas noter cette commande.'));
        }

        $data = $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'order_id' => $order->id,
            'user_id'  => $request->user()->id,
            'shop_id'  => $order->shop_id,
            'rating'   => $data['rating'],
            'comment'  => $data['comment'] ?? null,
        ]);

        $order->shop->refreshReliabilityStats();

        return back()->with('success', __('Merci pour votre avis !'));
    }
}
