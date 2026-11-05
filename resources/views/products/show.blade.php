@extends('layouts.app')

@section('title', $product->name.' — '.setting('site_name', 'MultiShop'))

@php
    $waText = rawurlencode('🛍️ '.$product->name.' — '.fcfa($product->price).' '.__('sur').' '.setting('site_name', 'MultiShop').' : '.route('products.show', $product));
@endphp

@section('content')
<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Accueil') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ __('Produits') }}</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ route('products.index', ['categorie' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    @unless ($product->is_visible)
        <div class="alert alert-warning"><i class="bi bi-eye-slash me-2"></i>{{ __('Ce produit a été masqué par la modération. Seuls vous et les administrateurs pouvez le voir.') }}</div>
    @endunless

    <div class="row g-4 mb-5">
        {{-- Image --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm overflow-hidden anim-card">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="ms-product-img-lg w-100">
            </div>
        </div>

        {{-- Infos --}}
        <div class="col-lg-7">
            @if ($product->category)
                <span class="badge text-bg-light border mb-2"><i class="bi {{ $product->category->icon ?: 'bi-tag' }} me-1"></i>{{ $product->category->name }}</span>
            @endif
            <h1 class="h3 fw-bold">{{ $product->name }}</h1>

            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <a href="{{ route('shops.show', $product->shop) }}" class="text-decoration-none">
                    <i class="bi bi-shop me-1"></i>{{ $product->shop->name }}
                </a>
                @include('partials.reliability-badge', ['shop' => $product->shop])
                @if ($product->shop->rating_count > 0)
                    @include('partials.stars', ['rating' => $product->shop->rating_avg])
                    <small class="opacity-75">({{ $product->shop->rating_count }})</small>
                @endif
            </div>

            <div class="mb-3">
                <span class="display-6 fw-bold text-primary">{{ fcfa($product->price) }}</span>
                @if ($product->old_price)
                    <span class="text-decoration-line-through opacity-50 ms-2">{{ fcfa($product->old_price) }}</span>
                    <span class="badge bg-danger ms-1">-{{ $product->discount_percent }}%</span>
                @endif
            </div>

            <p class="mb-3">
                @if ($product->stock > 5)
                    <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('En stock') }}</span>
                @elseif ($product->stock > 0)
                    <span class="badge text-bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Plus que :count en stock !', ['count' => $product->stock]) }}</span>
                @else
                    <span class="badge text-bg-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Rupture de stock') }}</span>
                @endif
                <small class="opacity-50 ms-2"><i class="bi bi-eye me-1"></i>{{ $product->views_count }} {{ __('vues') }} · {{ $product->sales_count }} {{ __('vendus') }}</small>
            </p>

            <form action="{{ route('cart.add', $product) }}" method="POST" class="d-flex gap-2 align-items-stretch mb-3 flex-wrap">
                @csrf
                <input type="number" name="qty" value="1" min="1" max="{{ max($product->stock, 1) }}"
                       class="form-control" style="width: 90px;" @disabled($product->stock < 1)>
                <button class="btn btn-primary btn-lg px-4 flex-grow-1 flex-md-grow-0" @disabled($product->stock < 1)>
                    <i class="bi bi-cart-plus me-2"></i>{{ __('Ajouter au panier') }}
                </button>
                <a class="btn btn-success btn-lg" target="_blank" rel="noopener"
                   href="https://wa.me/?text={{ $waText }}">
                    <i class="bi bi-whatsapp me-1"></i>{{ __('Partager') }}
                </a>
            </form>

            @if ($product->description)
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">{{ __('Description') }}</h6>
                        <p class="mb-0 opacity-75" style="white-space: pre-line;">{{ $product->description }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Produits similaires --}}
    @if ($similar->isNotEmpty())
        <section class="mb-5">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-collection me-2 text-primary"></i>{{ __('Produits similaires') }}</h2>
            <div class="row g-3 row-cols-2 row-cols-md-4">
                @foreach ($similar as $p)
                    <div class="col">@include('partials.product-card', ['product' => $p])</div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Avis de la boutique --}}
    @if ($reviews->isNotEmpty())
        <section>
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-chat-square-heart me-2 text-primary"></i>{{ __('Ce que disent les clients de cette boutique') }}</h2>
            <div class="row g-3 row-cols-1 row-cols-md-2">
                @foreach ($reviews as $review)
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small">{{ $review->user->first_name ?: $review->user->name }}</strong>
                                    @include('partials.stars', ['rating' => $review->rating])
                                </div>
                                <p class="small opacity-75 mb-0">{{ $review->comment }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
