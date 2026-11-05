<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Toutes les commandes du site. */
    public function index(Request $request)
    {
        $status  = $request->query('statut');
        $payment = $request->query('paiement');

        $orders = Order::with(['user', 'shop', 'items'])
            ->when(in_array($status, Order::STATUSES), fn ($q) => $q->where('status', $status))
            ->when(in_array($payment, ['card', 'paypal', 'cash']), fn ($q) => $q->where('payment_method', $payment))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'count'   => Order::count(),
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
        ];

        return view('admin.orders', compact('orders', 'status', 'payment', 'totals'));
    }
}
