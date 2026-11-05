@extends('layouts.app')

@section('title', __('Paiement par carte').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 540px;">
    <div class="text-center mb-4">
        <i class="bi bi-credit-card-2-front-fill display-4 text-primary"></i>
        <h1 class="h4 fw-bold mt-2">{{ __('Paiement par carte') }}</h1>
        <p class="opacity-75 mb-0">{{ __('Montant à payer') }} : <strong class="text-primary">{{ fcfa($total) }}</strong></p>
    </div>

    <div class="alert alert-info small">
        <i class="bi bi-info-circle me-1"></i>
        {{ __('Mode démonstration : aucun vrai paiement n\'est effectué.') }}
        {{ __('Carte de test') }} : <code>4242 4242 4242 4242</code>
    </div>

    <div class="card border-0 shadow ms-card-pay anim-card">
        <div class="card-body p-4">
            <form action="{{ route('payment.card', $group) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Titulaire de la carte') }}</label>
                    <input type="text" name="card_holder" class="form-control @error('card_holder') is-invalid @enderror"
                           value="{{ old('card_holder') }}" required placeholder="NOM PRÉNOM">
                    @error('card_holder')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Numéro de carte') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                        <input type="text" name="card_number" id="card-number" inputmode="numeric" maxlength="23"
                               class="form-control @error('card_number') is-invalid @enderror"
                               value="{{ old('card_number') }}" required placeholder="4242 4242 4242 4242">
                        @error('card_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label">{{ __('Expiration (MM/AA)') }}</label>
                        <input type="text" name="card_expiry" id="card-expiry" maxlength="7"
                               class="form-control @error('card_expiry') is-invalid @enderror"
                               value="{{ old('card_expiry') }}" required placeholder="12/27">
                        @error('card_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVV</label>
                        <input type="password" name="card_cvv" inputmode="numeric" maxlength="4"
                               class="form-control @error('card_cvv') is-invalid @enderror" required placeholder="•••">
                        @error('card_cvv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-shield-lock me-2"></i>{{ __('Payer') }} {{ fcfa($total) }}
                </button>
            </form>
        </div>
    </div>

    <p class="text-center small opacity-50 mt-3">
        <i class="bi bi-shield-check me-1"></i>{{ __('Vous recevrez un code SMS pour confirmer le paiement.') }}
    </p>
</div>
@endsection
