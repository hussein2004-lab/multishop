@extends('layouts.app')

@section('title', $shop->name.' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">

    {{-- En-tête boutique --}}
    <div class="card border-0 shadow-sm mb-4 ms-shop-header">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ms-shop-avatar ms-shop-avatar-lg">{{ mb_strtoupper(mb_substr($shop->name, 0, 1)) }}</span>
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">{{ $shop->name }}</h1>
                    <p class="opacity-75 mb-2 small">
                        <i class="bi bi-geo-alt me-1"></i>{{ $shop->address }}
                    </p>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @include('partials.reliability-badge', ['shop' => $shop])
                        @if ($shop->rating_count > 0)
                            @include('partials.stars', ['rating' => $shop->rating_avg])
                            <small class="opacity-75">{{ number_format($shop->rating_avg, 1) }} ({{ $shop->rating_count }} {{ __('avis') }})</small>
                        @endif
                        <small class="opacity-75"><i class="bi bi-box2-heart me-1"></i>{{ $shop->delivered_count }} {{ __('commandes livrées') }}</small>
                    </div>
                </div>
                @if ($shop->whatsapp_link)
                    <a href="{{ $shop->whatsapp_link }}" target="_blank" rel="noopener" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i>{{ __('Contacter sur WhatsApp') }}
                    </a>
                @endif
            </div>
            @if ($shop->description)
                <p class="mt-3 mb-0 opacity-75">{{ $shop->description }}</p>
            @endif
        </div>
    </div>

    {{-- Filtres par catégorie de la boutique --}}
    @if ($shopCategories->count() > 1)
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('shops.show', $shop) }}"
               class="btn btn-sm rounded-pill {{ ! $categorySlug ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('Tout') }}</a>
            @foreach ($shopCategories as $cat)
                <a href="{{ route('shops.show', [$shop, 'categorie' => $cat->slug]) }}"
                   class="btn btn-sm rounded-pill {{ $categorySlug === $cat->slug ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi {{ $cat->icon ?: 'bi-tag' }} me-1"></i>{{ $cat->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Produits --}}
    @if ($products->isEmpty())
        <div class="text-center py-5 opacity-50">
            <i class="bi bi-box display-4"></i>
            <p class="mt-3">{{ __('Cette boutique n\'a pas encore de produits dans cette catégorie.') }}</p>
        </div>
    @else
        <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
            @foreach ($products as $product)
                <div class="col">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
        {{ $products->links() }}
    @endif

    {{-- Avis --}}
    @if ($reviews->isNotEmpty())
        <section class="mt-5">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-chat-square-heart me-2 text-primary"></i>{{ __('Avis des clients') }}</h2>
            <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3">
                @foreach ($reviews as $review)
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="small">{{ $review->user->first_name ?: $review->user->name }}</strong>
                                    @include('partials.stars', ['rating' => $review->rating])
                                </div>
                                <p class="small opacity-75 mb-1">{{ $review->comment }}</p>
                                <small class="opacity-50">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
