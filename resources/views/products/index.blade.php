@extends('layouts.app')

@section('title', __('Produits').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-grid me-2 text-primary"></i>{{ __('Tous les produits') }}</h1>
    <p class="opacity-75 mb-4">{{ __('Comparez les offres de toutes les boutiques.') }}</p>

    {{-- Filtres --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold">{{ __('Rechercher') }}</label>
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Nom du produit...') }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold">{{ __('Catégorie') }}</label>
                <select name="categorie" class="form-select">
                    <option value="">{{ __('Toutes') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($catSlug === $cat->slug)>{{ $cat->name }} ({{ $cat->products_count }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label small fw-semibold">{{ __('Prix min (FCFA)') }}</label>
                <input type="number" name="prix_min" value="{{ $priceMin }}" min="0" step="500" class="form-control">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label small fw-semibold">{{ __('Prix max (FCFA)') }}</label>
                <input type="number" name="prix_max" value="{{ $priceMax }}" min="0" step="500" class="form-control">
            </div>
            <div class="col-lg-1 col-md-2">
                <label class="form-label small fw-semibold">{{ __('Trier par') }}</label>
                <select name="tri" class="form-select">
                    <option value="tendance" @selected($sort === 'tendance')>🔥 {{ __('Tendance') }}</option>
                    <option value="recent" @selected($sort === 'recent')>{{ __('Nouveautés') }}</option>
                    <option value="prix_asc" @selected($sort === 'prix_asc')>{{ __('Prix croissant') }}</option>
                    <option value="prix_desc" @selected($sort === 'prix_desc')>{{ __('Prix décroissant') }}</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </form>

    @if ($products->isEmpty())
        <div class="text-center py-5 opacity-50">
            <i class="bi bi-box display-4"></i>
            <p class="mt-3">{{ __('Aucun produit ne correspond à ces critères.') }}</p>
            <a href="{{ route('assistant', ['q' => $q]) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-stars me-1"></i>{{ __('Demander à l\'assistant IA') }}
            </a>
        </div>
    @else
        <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
            @foreach ($products as $product)
                <div class="col">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
        {{ $products->links() }}
    @endif
</div>
@endsection
