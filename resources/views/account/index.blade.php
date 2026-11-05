@extends('layouts.app')

@section('title', __('Mon compte').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 980px;">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-person-circle me-2 text-primary"></i>{{ __('Mon compte') }}</h1>

    <div class="row g-4">
        {{-- Profil --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-person-gear me-2 text-primary"></i>{{ __('Mon profil') }}</div>
                <div class="card-body">
                    <form action="{{ route('account.update') }}" method="POST" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Nom') }} *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Prénom') }}</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Nom d\'utilisateur (optionnel)') }}</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}">
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Téléphone') }}</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">{{ __('E-mail') }} *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12"><hr class="my-1"><small class="opacity-50">{{ __('Changer de mot de passe (laissez vide pour ne rien changer)') }}</small></div>
                        <div class="col-md-4">
                            <label class="form-label small">{{ __('Mot de passe actuel') }}</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">{{ __('Nouveau mot de passe') }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">{{ __('Confirmation') }}</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ __('Enregistrer') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Parrainage --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4 ms-referral-card">
                <div class="card-header bg-transparent fw-semibold">🎁 {{ __('Parrainage : invitez, gagnez !') }}</div>
                <div class="card-body">
                    <p class="small opacity-75 mb-2">
                        {{ __('Partagez votre code : votre ami et vous recevez chacun :amount de crédit.', ['amount' => fcfa((int) setting('referral_bonus', 500))]) }}
                    </p>
                    <div class="input-group mb-2">
                        <span class="input-group-text fw-bold font-monospace">{{ $user->referral_code }}</span>
                        <input type="text" class="form-control small" id="referral-link" value="{{ $referralLink }}" readonly>
                        <button class="btn btn-outline-primary" type="button" data-copy="#referral-link" title="{{ __('Copier') }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <a class="btn btn-success" target="_blank" rel="noopener"
                           href="https://wa.me/?text={{ rawurlencode(__('Rejoins-moi sur MultiShop ! Avec mon code :code tu reçois un crédit de bienvenue 🎁 ', ['code' => $user->referral_code]).$referralLink) }}">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="opacity-75">{{ __('Crédit disponible') }} : <strong class="text-success">{{ fcfa($user->store_credit) }}</strong></span>
                        <span class="opacity-75">{{ $referrals->count() }} {{ __('filleul(s)') }}</span>
                    </div>
                </div>
            </div>

            {{-- Fidélité --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">🏆 {{ __('Mes points de fidélité') }}</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="display-6 fw-bold text-primary">{{ $user->loyalty_points }}</span>
                        <div class="small opacity-75">
                            {{ __('points') }} ≈ <strong>{{ fcfa($user->loyalty_points * $pointValue) }}</strong><br>
                            {{ __('1 point offert par tranche de 1 000 FCFA d\'achat livré.') }}
                        </div>
                    </div>
                    @if ($loyaltyLog->isNotEmpty())
                        <ul class="list-group list-group-flush small">
                            @foreach ($loyaltyLog as $log)
                                <li class="list-group-item d-flex justify-content-between px-0 bg-transparent">
                                    <span class="opacity-75">{{ $log->description }}</span>
                                    <strong class="{{ $log->points > 0 ? 'text-success' : 'text-danger' }}">{{ $log->points > 0 ? '+' : '' }}{{ $log->points }} pts</strong>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
