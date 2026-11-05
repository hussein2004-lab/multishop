@extends('layouts.app')

@section('title', __('Boutique en attente').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card border-0 shadow text-center anim-card">
        <div class="card-body p-5">
            @if ($shop && $shop->status === 'rejected')
                <i class="bi bi-x-octagon display-3 text-danger"></i>
                <h1 class="h4 fw-bold mt-3">{{ __('Inscription refusée') }}</h1>
                <p class="opacity-75">{{ __('Votre demande de boutique ":name" n\'a pas été acceptée.', ['name' => $shop->name]) }}</p>
                @if ($shop->rejection_reason)
                    <div class="alert alert-danger small text-start">
                        <strong>{{ __('Motif') }} :</strong> {{ $shop->rejection_reason }}
                    </div>
                @endif
                <p class="small opacity-75">{{ __('Contactez-nous pour plus d\'informations :') }} {{ setting('contact_email', 'contact@multishop.ci') }}</p>
            @else
                <i class="bi bi-hourglass-split display-3 text-warning"></i>
                <h1 class="h4 fw-bold mt-3">{{ __('Votre boutique est en cours de vérification') }}</h1>
                <p class="opacity-75">
                    {{ __('Merci :name ! Notre équipe examine votre demande pour ":shop". Vous pourrez vendre dès qu\'un administrateur l\'aura approuvée (généralement sous 48 h).', ['name' => auth()->user()->first_name, 'shop' => $shop?->name]) }}
                </p>
            @endif
            <a href="{{ route('home') }}" class="btn btn-outline-primary mt-2">{{ __('Retour à l\'accueil') }}</a>
        </div>
    </div>
</div>
@endsection
