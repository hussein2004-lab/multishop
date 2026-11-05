<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

class DashboardController extends Controller
{
    /** Tableau de bord : toutes les statistiques du site. */
    public function index()
    {
        $stats = [
            'revenue'      => Order::where('payment_status', 'paid')->sum('total'),
            'orders'       => Order::count(),
            'users'        => User::count(),
            'shops'        => Shop::approved()->count(),
            'pendingShops' => Shop::where('status', 'pending')->count(),
            'products'     => Product::count(),
        ];

        $ordersByStatus = Order::selectRaw('status, COUNT(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        // Chiffre d'affaires des 6 derniers mois (pour le graphique)
        $start = now()->subMonths(5)->startOfMonth();

        $paidOrders = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->get(['total', 'created_at']);

        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key   = $month->format('Y-m');
            $monthlyRevenue[$key] = [
                'label' => $month->translatedFormat('M Y'),
                'total' => 0,
            ];
        }

        foreach ($paidOrders as $order) {
            $key = $order->created_at->format('Y-m');
            if (isset($monthlyRevenue[$key])) {
                $monthlyRevenue[$key]['total'] += $order->total;
            }
        }

        $topProducts = Product::with('shop')->orderByDesc('sales_count')->take(5)->get();

        $recentOrders = Order::with(['user', 'shop'])->latest()->take(8)->get();

        $pendingShopsList = Shop::with('user')->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'ordersByStatus', 'monthlyRevenue', 'topProducts', 'recentOrders', 'pendingShopsList'
        ));
    }
}
