@extends('layouts.app')

@section('title', __('Connexion').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-5" style="max-width: 440px;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="logo" style="height: 72px;">
        <h1 class="h4 fw-bold mt-3">{{ __('Bon retour parmi nous !') }}</h1>
    </div>

    <div class="card border-0 shadow anim-card">
        <div class="card-body p-4">
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('E-mail ou nom d\'utilisateur') }}</label>
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                           value="{{ old('login') }}" required autofocus>
                    @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Mot de passe') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">{{ __('Se souvenir de moi') }}</label>
                </div>
                <button class="btn btn-primary btn-lg w-100">{{ __('Connexion') }}</button>
            </form>
        </div>
    </div>

    <p class="text-center mt-3 small">
        {{ __('Pas encore de compte ?') }} <a href="{{ route('register') }}">{{ __('Inscription') }}</a>
        · <a href="{{ route('register.seller') }}">{{ __('Ouvrir ma boutique') }}</a>
    </p>
</div>
@endsection
