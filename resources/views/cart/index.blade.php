@extends('layouts.app')

@section('title', __('Mon panier').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-cart3 me-2 text-primary"></i>{{ __('Mon panier') }}</h1>

    @if ($items->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 opacity-25"></i>
            <p class="mt-3 opacity-75">{{ __('Votre panier est vide.') }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary"><i class="bi bi-grid me-1"></i>{{ __('Découvrir les produits') }}</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach ($byShop as $shopId => $shopItems)
                    @php $shop = $shopItems->first()['product']->shop; @endphp
                    <div class="card border-0 shadow-sm mb-3 anim-card">
                        <div class="card-header bg-transparent d-flex align-items-center gap-2">
                            <i class="bi bi-shop text-primary"></i>
                            <a href="{{ route('shops.show', $shop) }}" class="fw-semibold text-decoration-none">{{ $shop->name }}</a>
                            @include('partials.reliability-badge', ['shop' => $shop])
                        </div>
                        <div class="card-body p-0">
                            @foreach ($shopItems as $item)
                                <div class="d-flex align-items-center gap-3 p-3 border-bottom ms-cart-row">
                                    <img src="{{ $item['product']->image_url }}" alt="" class="ms-cart-thumb rounded">
                                    <div class="flex-grow-1 min-w-0">
                                        <a href="{{ route('products.show', $item['product']) }}" class="text-decoration-none">
                                            <div class="fw-semibold text-truncate">{{ $item['product']->name }}</div>
                                        </a>
                                        <small class="opacity-75">{{ fcfa($item['product']->price) }} / {{ __('unité') }}</small>
                                    </div>
                                    <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="d-flex align-items-center gap-1">
                                        @csrf @method('PATCH')
                                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="0" max="{{ $item['product']->stock }}"
                                               class="form-control form-control-sm" style="width: 72px;"
                                               onchange="this.form.submit()">
                                    </form>
                                    <div class="fw-bold text-primary text-nowrap" style="min-width: 110px; text-align: end;">{{ fcfa($item['line']) }}</div>
                                    <form action="{{ route('cart.remove', $item['product']->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Retirer') }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Résumé --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm ms-summary-card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">{{ __('Résumé') }}</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="opacity-75">{{ __('Sous-total') }}</span>
                            <span>{{ fcfa($subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="opacity-75">{{ __('Livraison') }} ({{ $byShop->count() }} {{ __('boutique(s)') }})</span>
                            <span>{{ fcfa($deliveryFee) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                            <span>{{ __('Total') }}</span>
                            <span class="text-primary">{{ fcfa($total) }}</span>
                        </div>
                        @auth
                            <a href="{{ route('checkout.show') }}" class="btn btn-primary btn-lg w-100">
                                {{ __('Passer la commande') }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100 mb-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('Connectez-vous pour commander') }}
                            </a>
                            <small class="d-block text-center opacity-75">
                                {{ __('Pas encore de compte ?') }} <a href="{{ route('register') }}">{{ __('Inscription') }}</a>
                            </small>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
