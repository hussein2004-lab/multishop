@extends('layouts.app')

@section('title', __('Ouvrir ma boutique').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-5" style="max-width: 680px;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="logo" style="height: 64px;">
        <h1 class="h4 fw-bold mt-3"><i class="bi bi-shop me-2"></i>{{ __('Ouvrir ma boutique sur MultiShop') }}</h1>
        <p class="opacity-75 small">{{ __('Inscription gratuite. Votre boutique sera vérifiée par notre équipe avant publication.') }}</p>
    </div>

    <div class="card border-0 shadow anim-card">
        <div class="card-body p-4">
            <form action="{{ route('register.seller.post') }}" method="POST">
                @csrf
                <h6 class="fw-bold mb-3 text-primary">1. {{ __('Vos informations') }}</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Nom') }} *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Prénom') }} *</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('E-mail') }} *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Téléphone') }} *</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required placeholder="+225 ...">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Nom d\'utilisateur (optionnel)') }}</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Mot de passe') }} *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Confirmer le mot de passe') }} *</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-primary">2. {{ __('Votre boutique') }}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Nom de la boutique') }} *</label>
                        <input type="text" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror" value="{{ old('shop_name') }}" required>
                        @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('WhatsApp boutique (optionnel)') }}</label>
                        <input type="tel" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}" placeholder="225XXXXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Ville / Commune') }} *</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" required placeholder="Cocody, Abidjan...">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Adresse') }} *</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" required>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Décrivez votre boutique') }} *</label>
                        <textarea name="shop_description" rows="3" class="form-control @error('shop_description') is-invalid @enderror"
                                  required placeholder="{{ __('Que vendez-vous ? Quels sont vos points forts ?') }}">{{ old('shop_description') }}</textarea>
                        @error('shop_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button class="btn btn-primary btn-lg w-100 mt-4">
                    <i class="bi bi-send me-2"></i>{{ __('Envoyer ma demande') }}
                </button>
                <small class="d-block text-center mt-2 opacity-50">{{ __('Votre demande sera examinée par un administrateur sous 48 h.') }}</small>
            </form>
        </div>
    </div>
</div>
@endsection
