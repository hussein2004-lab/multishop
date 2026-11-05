@extends('layouts.app')

@section('title', __('Assistant IA').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 960px;">

    <div class="text-center mb-4">
        <div class="ms-ai-avatar mx-auto mb-3"><i class="bi bi-stars"></i></div>
        <h1 class="h3 fw-bold">{{ __('Assistant IA MultiShop') }}</h1>
        <p class="opacity-75">{{ __('Décrivez ce que vous cherchez, avec votre budget, et je vous propose les meilleurs produits.') }}</p>
    </div>

    <form action="{{ route('assistant') }}" method="GET" class="mb-3">
        <div class="input-group input-group-lg shadow-sm">
            <span class="input-group-text"><i class="bi bi-magic"></i></span>
            <input type="search" name="q" class="form-control" required minlength="3"
                   value="{{ $query }}" placeholder="{{ __('Ex : je veux un téléphone à 100 000 FCFA') }}" autofocus>
            <button class="btn btn-primary px-4" type="submit">{{ __('Rechercher') }}</button>
        </div>
    </form>

    {{-- Suggestions --}}
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        @foreach ($suggestions as $suggestion)
            <a href="{{ route('assistant', ['q' => $suggestion]) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-chat-dots me-1"></i>{{ $suggestion }}
            </a>
        @endforeach
    </div>

    @if ($result)
        {{-- Réponse de l'IA --}}
        <div class="ms-ai-bubble shadow-sm mb-4 anim-card">
            <div class="d-flex gap-3">
                <div class="ms-ai-avatar ms-ai-avatar-sm flex-shrink-0"><i class="bi bi-stars"></i></div>
                <div>
                    <p class="mb-1">{{ $result['message'] }}</p>
                    <small class="opacity-50">
                        @if ($result['engine'] === 'claude')
                            <i class="bi bi-cloud-check me-1"></i>{{ __('Propulsé par Claude (Anthropic)') }}
                        @else
                            <i class="bi bi-cpu me-1"></i>{{ __('Moteur intelligent MultiShop') }}
                        @endif
                    </small>
                </div>
            </div>
        </div>

        @if ($result['products']->isNotEmpty())
            <div class="row g-3 row-cols-2 row-cols-md-3">
                @foreach ($result['products'] as $product)
                    <div class="col">@include('partials.product-card', ['product' => $product])</div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-search display-4"></i>
                <p class="mt-3">{{ __('Aucun produit trouvé. Essayez une autre formulation.') }}</p>
            </div>
        @endif
    @endif
</div>
@endsection
