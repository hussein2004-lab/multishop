@extends('layouts.app')

@section('title', __('Inscription').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="logo" style="height: 64px;">
        <h1 class="h4 fw-bold mt-3">{{ __('Créer mon compte') }}</h1>
        <p class="opacity-75 small">{{ __('Rejoignez MultiShop et commandez auprès des meilleures boutiques locales.') }}</p>
    </div>

    <div class="card border-0 shadow anim-card">
        <div class="card-body p-4">
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="row g-3">
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
                        <label class="form-label">{{ __('Nom d\'utilisateur (optionnel)') }}</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Téléphone') }} *</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required placeholder="+225 ...">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('E-mail') }} *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Mot de passe') }} *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Confirmer le mot de passe') }} *</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                    </div>
                    <div class="col-12">
                        <label class="form-label">🎁 {{ __('Code parrain (optionnel)') }}</label>
                        <input type="text" name="referral_code" class="form-control @error('referral_code') is-invalid @enderror"
                               value="{{ old('referral_code', $ref) }}" placeholder="MS-XXXXXX">
                        @error('referral_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="opacity-75">{{ __('Un ami vous a invité ? Entrez son code : vous recevez chacun un crédit de bienvenue !') }}</small>
                    </div>
                </div>
                <button class="btn btn-primary btn-lg w-100 mt-4">{{ __('Créer mon compte') }}</button>
            </form>
        </div>
    </div>

    <p class="text-center mt-3 small">
        {{ __('Déjà inscrit ?') }} <a href="{{ route('login') }}">{{ __('Connexion') }}</a>
    </p>
</div>
@endsection
