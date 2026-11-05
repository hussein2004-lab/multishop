@extends('layouts.app')

@section('title', __('Ma boutique').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0"><i class="bi bi-shop me-2 text-primary"></i>{{ $shop->name }}</h1>
            <small class="opacity-75">{{ __('Tableau de bord vendeur') }}</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @include('partials.reliability-badge', ['shop' => $shop])
            <a href="{{ route('shops.show', $shop) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="bi bi-eye me-1"></i>{{ __('Voir ma vitrine') }}
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['bi-box-seam', __('Produits'), $stats['products'], 'primary'],
            ['bi-receipt', __('Commandes'), $stats['orders'], 'info'],
            ['bi-hourglass-split', __('En attente'), $stats['pending'], 'warning'],
            ['bi-house-check', __('Livrées'), $stats['delivered'], 'success'],
            ['bi-cash-stack', __('Revenus'), fcfa($stats['revenue']), 'success'],
            ['bi-star-fill', __('Note moyenne'), $stats['rating'] ? number_format($stats['rating'], 1).' / 5' : '—', 'warning'],
        ] as [$icon, $label, $value, $color])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 ms-stat-card anim-card">
                    <div class="card-body py-3">
                        <i class="bi {{ $icon }} text-{{ $color }} fs-4"></i>
                        <div class="fw-bold ms-stat-value">{{ $value }}</div>
                        <small class="opacity-75">{{ $label }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Commandes récentes --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ __('Commandes récentes') }}</span>
                    <a href="{{ route('seller.orders.index') }}" class="small">{{ __('Tout voir') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="small opacity-75">
                            <th>#</th><th>{{ __('Client') }}</th><th>{{ __('Articles') }}</th><th>{{ __('Total') }}</th><th>{{ __('Statut') }}</th><th></th>
                        </tr></thead>
                        <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->delivery_name }}</td>
                                <td>{{ $order->items->sum('quantity') }}</td>
                                <td class="fw-semibold">{{ fcfa($order->total) }}</td>
                                <td><span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span></td>
                                <td><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 opacity-50">{{ __('Aucune commande pour le moment.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Stock faible + raccourcis --}}
        <div class="col-lg-4">
            <a href="{{ route('seller.products.create') }}" class="btn btn-primary btn-lg w-100 mb-3">
                <i class="bi bi-plus-circle me-2"></i>{{ __('Ajouter un produit') }}
            </a>
            @if ($lowStock->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Stock faible') }}
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($lowStock as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <a href="{{ route('seller.products.edit', $product) }}" class="text-decoration-none text-truncate me-2">{{ $product->name }}</a>
                                <span class="badge text-bg-{{ $product->stock === 0 ? 'danger' : 'warning' }}">{{ $product->stock }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
