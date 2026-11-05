@extends('layouts.app')

@section('title', __('Vérification SMS').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 480px;">
    <div class="text-center mb-4">
        <i class="bi bi-phone-vibrate display-4 text-primary"></i>
        <h1 class="h4 fw-bold mt-2">{{ __('Vérification par SMS') }}</h1>
        <p class="opacity-75">
            {{ __('Un code à 6 chiffres a été envoyé au') }} <strong>{{ $phone }}</strong><br>
            {{ __('Montant') }} : <strong class="text-primary">{{ fcfa($total) }}</strong>
        </p>
    </div>

    @if ($demoCode)
        <div class="alert alert-warning text-center">
            <i class="bi bi-lightbulb me-1"></i>{{ __('Mode démonstration — votre code est') }} :
            <span class="fs-4 fw-bold font-monospace d-block mt-1">{{ $demoCode }}</span>
        </div>
    @endif

    <div class="card border-0 shadow anim-card">
        <div class="card-body p-4">
            <form action="{{ route('payment.otp.verify', $group) }}" method="POST">
                @csrf
                <label class="form-label text-center w-100">{{ __('Entrez le code reçu') }}</label>
                <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required
                       class="form-control form-control-lg text-center fs-2 font-monospace ms-otp-input @error('code') is-invalid @enderror"
                       placeholder="······" autofocus autocomplete="one-time-code">
                @error('code')<div class="invalid-feedback text-center">{{ $message }}</div>@enderror
                <button class="btn btn-primary btn-lg w-100 mt-3">
                    <i class="bi bi-check-circle me-1"></i>{{ __('Confirmer le paiement') }}
                </button>
            </form>
            <form action="{{ route('payment.otp.resend', $group) }}" method="POST" class="text-center mt-3">
                @csrf
                <button class="btn btn-link btn-sm">{{ __('Renvoyer le code') }}</button>
            </form>
        </div>
    </div>

    <p class="text-center small opacity-50 mt-3">{{ __('Le code est valable 5 minutes (3 essais maximum).') }}</p>
</div>
@endsection
