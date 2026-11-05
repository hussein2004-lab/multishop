@extends('layouts.app')

@section('title', 'PayPal — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 480px;">
    <div class="text-center mb-4">
        <i class="bi bi-paypal display-4" style="color: #0070ba;"></i>
        <h1 class="h4 fw-bold mt-2">{{ __('Connexion à PayPal') }}</h1>
        <p class="opacity-75 mb-0">{{ __('Montant à payer') }} : <strong class="text-primary">{{ fcfa($total) }}</strong></p>
    </div>

    <div class="alert alert-info small">
        <i class="bi bi-info-circle me-1"></i>{{ __('Mode démonstration : aucun vrai paiement n\'est effectué.') }}
    </div>

    <div class="card border-0 shadow anim-card ms-paypal-card">
        <div class="card-body p-4">
            <form action="{{ route('payment.paypal', $group) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('E-mail PayPal') }}</label>
                    <input type="email" name="paypal_email" class="form-control @error('paypal_email') is-invalid @enderror"
                           value="{{ old('paypal_email') }}" required placeholder="email@exemple.com">
                    @error('paypal_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">{{ __('Mot de passe') }}</label>
                    <input type="password" name="paypal_password" class="form-control @error('paypal_password') is-invalid @enderror" required>
                    @error('paypal_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-lg w-100 text-white fw-semibold" style="background: #0070ba;">
                    {{ __('Se connecter et payer') }}
                </button>
            </form>
        </div>
    </div>

    <p class="text-center small opacity-50 mt-3">
        <i class="bi bi-shield-check me-1"></i>{{ __('Vous recevrez un code SMS pour confirmer le paiement.') }}
    </p>
</div>
@endsection
