@extends('layouts.app')

@section('title', __('Commande confirmée').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-5" style="max-width: 640px;">
    <div class="text-center mb-4">
        <div class="ms-success-check mx-auto mb-3"><i class="bi bi-check-lg"></i></div>
        <h1 class="h3 fw-bold">{{ __('Merci pour votre commande !') }}</h1>
        <p class="opacity-75">
            {{ __('Référence') }} : <strong class="font-monospace">{{ $group }}</strong>
        </p>
    </div>

    @foreach ($orders as $order)
        <div class="card border-0 shadow-sm mb-3 anim-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <span class="fw-semibold"><i class="bi bi-shop me-1 text-primary"></i>{{ $order->shop->name }}</span>
                    <span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span>
                </div>
                <ul class="small opacity-75 mb-2 ps-3">
                    @foreach ($order->items as $item)
                        <li>{{ $item->quantity }} × {{ $item->product_name }}</li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-between small">
                    <span>
                        @if ($order->payment_method === 'cash')
                            <i class="bi bi-cash-coin me-1"></i>{{ __('Paiement à la livraison') }}
                        @elseif ($order->payment_status === 'paid')
                            <i class="bi bi-check-circle text-success me-1"></i>{{ __('Payé en ligne') }}
                        @else
                            <i class="bi bi-hourglass-split text-warning me-1"></i>{{ __('Paiement en attente') }}
                        @endif
                    </span>
                    <strong>{{ fcfa($order->total) }}</strong>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between fw-bold fs-5 px-2 mb-4">
        <span>{{ __('Total') }}</span><span class="text-primary">{{ fcfa($total) }}</span>
    </div>

    @if ($orders->first()->isAwaitingOnlinePayment())
        <a href="{{ route('payment.show', $group) }}" class="btn btn-warning btn-lg w-100 mb-2">
            <i class="bi bi-credit-card me-1"></i>{{ __('Terminer le paiement') }}
        </a>
    @endif

    <div class="d-flex gap-2">
        <a href="{{ route('orders.index') }}" class="btn btn-primary flex-grow-1"><i class="bi bi-box-seam me-1"></i>{{ __('Mes commandes') }}</a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary flex-grow-1">{{ __('Continuer mes achats') }}</a>
    </div>
</div>
@endsection
