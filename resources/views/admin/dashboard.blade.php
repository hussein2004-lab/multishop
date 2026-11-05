@extends('layouts.app')

@section('title', __('Administration').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>{{ __('Administration') }}</h1>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-primary btn-sm position-relative">
                <i class="bi bi-shop me-1"></i>{{ __('Boutiques') }}
                @if ($stats['pendingShops'] > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['pendingShops'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-people me-1"></i>{{ __('Utilisateurs') }}</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-seam me-1"></i>{{ __('Produits') }}</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt me-1"></i>{{ __('Commandes') }}</a>
            <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-badge me-1"></i>{{ __('Administrateurs') }}</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-gear me-1"></i>{{ __('Paramètres') }}</a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['bi-cash-stack', __('Chiffre d\'affaires'), fcfa($stats['revenue']), 'success', $stats['revenue']],
            ['bi-receipt', __('Commandes'), $stats['orders'], 'primary', $stats['orders']],
            ['bi-people', __('Utilisateurs'), $stats['users'], 'info', $stats['users']],
            ['bi-shop', __('Boutiques actives'), $stats['shops'], 'primary', $stats['shops']],
            ['bi-hourglass-split', __('Boutiques en attente'), $stats['pendingShops'], 'warning', $stats['pendingShops']],
            ['bi-box-seam', __('Produits'), $stats['products'], 'secondary', $stats['products']],
        ] as [$icon, $label, $display, $color, $raw])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 ms-stat-card anim-card">
                    <div class="card-body py-3">
                        <i class="bi {{ $icon }} text-{{ $color }} fs-4"></i>
                        <div class="fw-bold ms-stat-value" data-counter="{{ is_numeric($raw) ? $raw : '' }}">{{ $display }}</div>
                        <small class="opacity-75">{{ $label }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        {{-- Graphique CA --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-graph-up-arrow me-2 text-success"></i>{{ __('Chiffre d\'affaires (6 derniers mois)') }}</div>
                <div class="card-body"><canvas id="revenue-chart" height="110"></canvas></div>
            </div>
        </div>
        {{-- Donut statuts --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>{{ __('Commandes par statut') }}</div>
                <div class="card-body d-flex align-items-center justify-content-center"><canvas id="status-chart" style="max-height: 230px;"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Boutiques en attente --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-hourglass-split me-1 text-warning"></i>{{ __('Boutiques à valider') }}</span>
                    <a href="{{ route('admin.shops.index') }}" class="small">{{ __('Tout voir') }}</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($pendingShopsList as $shop)
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <strong class="d-block text-truncate">{{ $shop->name }}</strong>
                                <small class="opacity-75">{{ $shop->user->full_name }} · {{ $shop->city }}</small>
                            </div>
                            <form action="{{ route('admin.shops.approve', $shop) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> {{ __('Approuver') }}</button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent text-center py-4 opacity-50">{{ __('Aucune boutique en attente.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Top produits --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-fire me-1 text-danger"></i>{{ __('Top produits') }}</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="small opacity-75"><th>{{ __('Produit') }}</th><th>{{ __('Boutique') }}</th><th>{{ __('Prix') }}</th><th>{{ __('Ventes') }}</th></tr></thead>
                        <tbody>
                        @foreach ($topProducts as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->name }}</td>
                                <td><small class="opacity-75">{{ $product->shop->name }}</small></td>
                                <td>{{ fcfa($product->price) }}</td>
                                <td><span class="badge text-bg-primary">{{ $product->sales_count }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Commandes récentes --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ __('Commandes récentes') }}</span>
                    <a href="{{ route('admin.orders.index') }}" class="small">{{ __('Tout voir') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="small opacity-75">
                            <th>#</th><th>{{ __('Client') }}</th><th>{{ __('Boutique') }}</th><th>{{ __('Total') }}</th><th>{{ __('Paiement') }}</th><th>{{ __('Statut') }}</th><th>{{ __('Date') }}</th><th></th>
                        </tr></thead>
                        <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->user->full_name }}</td>
                                <td><small class="opacity-75">{{ $order->shop->name }}</small></td>
                                <td class="fw-semibold">{{ fcfa($order->total) }}</td>
                                <td><small>{{ __('paiement.'.$order->payment_method) }}</small></td>
                                <td><span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span></td>
                                <td><small>{{ $order->created_at->translatedFormat('d M H:i') }}</small></td>
                                <td><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const styles = getComputedStyle(document.body);
    const textColor = styles.getPropertyValue('--ms-text') || '#212529';

    new Chart(document.getElementById('revenue-chart'), {
        type: 'line',
        data: {
            labels: @json(array_column($monthlyRevenue, 'label')),
            datasets: [{
                label: 'FCFA',
                data: @json(array_column($monthlyRevenue, 'total')),
                borderColor: '#1565d8',
                backgroundColor: 'rgba(21, 101, 216, .15)',
                fill: true,
                tension: .35,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { color: textColor }, beginAtZero: true },
                x: { ticks: { color: textColor } }
            }
        }
    });

    const statusData = @json($ordersByStatus);
    new Chart(document.getElementById('status-chart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(s => ({
                pending: @json(__('statut.pending')), confirmed: @json(__('statut.confirmed')),
                shipped: @json(__('statut.shipped')), delivered: @json(__('statut.delivered')),
                cancelled: @json(__('statut.cancelled')),
            }[s] || s)),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: ['#ffc107', '#0dcaf0', '#1565d8', '#198754', '#dc3545'],
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { color: textColor } } } }
    });
});
</script>
@endpush
