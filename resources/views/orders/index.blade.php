@extends('layouts.app')

@section('title', __('Mes commandes').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-box-seam me-2 text-primary"></i>{{ __('Mes commandes') }}</h1>

    @if ($orders->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-box display-1 opacity-25"></i>
            <p class="mt-3 opacity-75">{{ __('Vous n\'avez pas encore passé de commande.') }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">{{ __('Découvrir les produits') }}</a>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($orders as $order)
                <div class="card border-0 shadow-sm anim-card">
                    <div class="card-body d-flex flex-wrap align-items-center gap-3">
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <strong>#{{ $order->id }}</strong>
                                <span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span>
                                <span class="badge text-bg-light border">
                                    @if ($order->payment_method === 'cash') <i class="bi bi-cash-coin me-1"></i> @elseif($order->payment_method === 'paypal') <i class="bi bi-paypal me-1"></i> @else <i class="bi bi-credit-card me-1"></i> @endif
                                    {{ __('paiement.'.$order->payment_method) }} — {{ __('paiement_statut.'.$order->payment_status) }}
                                </span>
                            </div>
                            <div class="small opacity-75">
                                <i class="bi bi-shop me-1"></i>{{ $order->shop->name }}
                                · {{ $order->items->sum('quantity') }} {{ __('article(s)') }}
                                · {{ $order->created_at->translatedFormat('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="fw-bold text-primary fs-5">{{ fcfa($order->total) }}</div>
                        <div class="d-flex gap-2">
                            @if ($order->isAwaitingOnlinePayment())
                                <a href="{{ route('payment.show', $order->group_code) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-credit-card me-1"></i>{{ __('Payer') }}
                                </a>
                            @endif
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                {{ __('Détails') }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
