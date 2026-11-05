@extends('layouts.app')

@section('title', __('Boutiques').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-shop me-2 text-primary"></i>{{ __('Nos boutiques') }}</h1>
    <p class="opacity-75 mb-4">{{ __('Des commerçants locaux vérifiés, notés par de vrais clients.') }}</p>

    {{-- Filtres --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">{{ __('Rechercher') }}</label>
                <input type="search" name="q" value="{{ $q }}" class="form-control"
                       placeholder="{{ __('Nom de boutique, ville...') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold"><i class="bi bi-shield-check me-1"></i>{{ __('Fiabilité du vendeur') }}</label>
                <select name="fiabilite" class="form-select">
                    <option value="all" @selected($reliability === 'all')>{{ __('Toutes les boutiques') }}</option>
                    <option value="90" @selected($reliability === '90')>{{ __('Excellente') }} (≥ 90%)</option>
                    <option value="75" @selected($reliability === '75')>{{ __('Très bonne') }} (≥ 75%)</option>
                    <option value="50" @selected($reliability === '50')>{{ __('Correcte') }} (≥ 50%)</option>
                    <option value="new" @selected($reliability === 'new')>{{ __('Nouveaux vendeurs') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">{{ __('Trier par') }}</label>
                <select name="tri" class="form-select">
                    <option value="fiabilite" @selected($sort === 'fiabilite')>{{ __('Fiabilité') }}</option>
                    <option value="note" @selected($sort === 'note')>{{ __('Note clients') }}</option>
                    <option value="recent" @selected($sort === 'recent')>{{ __('Plus récentes') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>{{ __('Filtrer') }}</button>
            </div>
        </div>
    </form>

    @if ($shops->isEmpty())
        <div class="text-center py-5 opacity-50">
            <i class="bi bi-shop display-4"></i>
            <p class="mt-3">{{ __('Aucune boutique ne correspond à ces critères.') }}</p>
        </div>
    @else
        <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3">
            @foreach ($shops as $shop)
                <div class="col">@include('partials.shop-card', ['shop' => $shop])</div>
            @endforeach
        </div>
    @endif
</div>
@endsection
