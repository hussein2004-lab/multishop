@extends('layouts.app')

@section('content')
{{-- ============================== HERO ============================== --}}
<section class="ms-hero py-5">
    <div class="container py-lg-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold mb-3 ms-hero-title">
                    {{ __('Toutes vos boutiques locales,') }}<br>
                    <span class="text-primary">{{ __('une seule adresse.') }}</span>
                </h1>
                <p class="lead opacity-75 mb-4 ms-hero-sub">
                    {{ __('Commandez auprès des meilleures boutiques de votre ville, payez en ligne ou à la livraison, et faites-vous livrer où que vous soyez.') }}
                </p>

                {{-- Recherche IA --}}
                <form action="{{ route('assistant') }}" method="GET" class="ms-hero-search mb-3">
                    <div class="input-group input-group-lg shadow">
                        <span class="input-group-text border-0"><i class="bi bi-stars text-warning"></i></span>
                        <input type="search" name="q" class="form-control border-0"
                               placeholder="{{ __('Ex : je veux un téléphone à 100 000 FCFA') }}">
                        <button class="btn btn-primary px-4" type="submit">{{ __('Demander à l\'IA') }}</button>
                    </div>
                </form>
                <p class="small opacity-50 mb-0"><i class="bi bi-lightbulb me-1"></i>{{ __('Décrivez ce que vous voulez, notre IA trouve les meilleurs produits pour vous.') }}</p>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <img src="{{ asset('images/logo.png') }}" alt="MultiShop" class="ms-hero-logo img-fluid">
            </div>
        </div>
    </div>
</section>

<div class="container">

    {{-- ============================== CATÉGORIES ============================== --}}
    <section class="py-4">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            @foreach ($categories as $cat)
                <a href="{{ route('products.index', ['categorie' => $cat->slug]) }}"
                   class="btn btn-outline-secondary btn-sm rounded-pill ms-cat-chip anim-chip">
                    <i class="bi {{ $cat->icon ?: 'bi-tag' }} me-1"></i>{{ $cat->name }}
                    <span class="opacity-50">({{ $cat->products_count }})</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================== POUR VOUS ============================== --}}
    @if ($forYou->isNotEmpty())
        <section class="py-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>{{ __('Pour vous') }}</h2>
                <small class="opacity-50">{{ __('Sélectionné selon vos visites') }}</small>
            </div>
            <div class="row g-3 row-cols-2 row-cols-md-4">
                @foreach ($forYou as $product)
                    <div class="col">@include('partials.product-card', ['product' => $product])</div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================== TENDANCES ============================== --}}
    <section class="py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h4 fw-bold mb-0"><i class="bi bi-fire text-danger me-2"></i>{{ __('Produits tendance') }}</h2>
            <a href="{{ route('products.index', ['tri' => 'tendance']) }}" class="small">{{ __('Tout voir') }} <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3 row-cols-2 row-cols-md-4">
            @foreach ($trending as $product)
                <div class="col">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    </section>

    {{-- ============================== BOUTIQUES ============================== --}}
    <section class="py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h4 fw-bold mb-0"><i class="bi bi-shop me-2 text-primary"></i>{{ __('Nos meilleures boutiques') }}</h2>
            <a href="{{ route('shops.index') }}" class="small">{{ __('Toutes les boutiques') }} <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-4">
            @foreach ($topShops as $shop)
                <div class="col">@include('partials.shop-card', ['shop' => $shop])</div>
            @endforeach
        </div>
    </section>

    {{-- ============================== COMMENT ÇA MARCHE ============================== --}}
    <section class="py-5">
        <h2 class="h4 fw-bold text-center mb-4">{{ __('Comment ça marche ?') }}</h2>
        <div class="row g-4 text-center">
            @foreach ([
                ['bi-shop-window', __('1. Explorez les boutiques'), __('Parcourez les boutiques locales vérifiées et leurs produits.')],
                ['bi-cart-check', __('2. Remplissez votre panier'), __('Ajoutez vos articles, même de plusieurs boutiques à la fois.')],
                ['bi-geo-alt', __('3. Indiquez votre position'), __('Placez votre point de livraison sur la carte en un clic.')],
                ['bi-truck', __('4. Payez comme vous voulez'), __('Par carte, PayPal ou en espèces à la livraison.')],
            ] as [$icon, $title, $text])
                <div class="col-md-3 anim-card">
                    <div class="ms-step-icon mb-3"><i class="bi {{ $icon }}"></i></div>
                    <h6 class="fw-bold">{{ $title }}</h6>
                    <p class="small opacity-75 mb-0">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================== CTA VENDEUR ============================== --}}
    <section class="ms-cta-seller rounded-4 p-4 p-lg-5 text-center anim-card">
        <h2 class="h3 fw-bold mb-2">{{ __('Vous avez une boutique ?') }}</h2>
        <p class="opacity-75 mb-4">{{ __('Rejoignez MultiShop gratuitement et vendez vos produits à des milliers de clients.') }}</p>
        <a href="{{ route('register.seller') }}" class="btn btn-light btn-lg px-4 fw-semibold">
            <i class="bi bi-shop me-2"></i>{{ __('Ouvrir ma boutique') }}
        </a>
    </section>
</div>
@endsection
