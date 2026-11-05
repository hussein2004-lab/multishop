<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Transitions autorisées pour le vendeur. */
    private const TRANSITIONS = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped'   => ['delivered'],
    ];

    public function index(Request $request)
    {
        $shop   = $request->user()->shop;
        $status = $request->query('statut');

        $orders = $shop->orders()
            ->with(['user', 'items'])
            ->when($status && in_array($status, Order::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $counts = $shop->orders()
            ->selectRaw('status, COUNT(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        return view('seller.orders.index', compact('orders', 'status', 'counts'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($order->shop_id === $request->user()->shop->id, 403);

        $data = $request->validate([
            'status' => 'required|in:confirmed,shipped,delivered,cancelled',
        ]);

        $new = $data['status'];

        if (! in_array($new, self::TRANSITIONS[$order->status] ?? [])) {
            return back()->with('error', __('Changement de statut impossible.'));
        }

        match ($new) {
            'delivered' => $order->markDelivered(),
            'cancelled' => $order->markCancelled(),
            default     => $order->update(['status' => $new]),
        };

        return back()->with('success', __('Statut de la commande mis à jour.'));
    }
}
