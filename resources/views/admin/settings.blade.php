@extends('layouts.app')

@section('title', __('Paramètres du site').' — '.__('Administration'))

@section('content')
<div class="container py-4" style="max-width: 760px;">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-gear me-2 text-primary"></i>{{ __('Paramètres du site') }}</h1>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold"><i class="bi bi-globe me-2 text-primary"></i>{{ __('Identité du site') }}</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nom du site') }} *</label>
                    <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $settings['site_name']) }}" required>
                    @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Slogan') }}</label>
                    <input type="text" name="site_tagline" class="form-control" value="{{ old('site_tagline', $settings['site_tagline']) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('E-mail de contact') }}</label>
                    <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $settings['contact_email']) }}">
                    @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Téléphone de contact') }}</label>
                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings['contact_phone']) }}">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold"><i class="bi bi-truck me-2 text-primary"></i>{{ __('Commerce') }}</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Frais de livraison (FCFA, par boutique)') }} *</label>
                    <input type="number" name="delivery_fee" min="0" class="form-control @error('delivery_fee') is-invalid @enderror" value="{{ old('delivery_fee', $settings['delivery_fee']) }}" required>
                    @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nombre de produits proposés par l\'IA') }} *</label>
                    <input type="number" name="ai_max_results" min="3" max="24" class="form-control @error('ai_max_results') is-invalid @enderror" value="{{ old('ai_max_results', $settings['ai_max_results']) }}" required>
                    @error('ai_max_results')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">🏆 {{ __('Fidélité et parrainage') }}</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Points par 1 000 FCFA') }} *</label>
                    <input type="number" name="loyalty_earn_rate" min="0" class="form-control @error('loyalty_earn_rate') is-invalid @enderror" value="{{ old('loyalty_earn_rate', $settings['loyalty_earn_rate']) }}" required>
                    @error('loyalty_earn_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Valeur d\'un point (FCFA)') }} *</label>
                    <input type="number" name="loyalty_point_value" min="1" class="form-control @error('loyalty_point_value') is-invalid @enderror" value="{{ old('loyalty_point_value', $settings['loyalty_point_value']) }}" required>
                    @error('loyalty_point_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Bonus parrainage (FCFA)') }} *</label>
                    <input type="number" name="referral_bonus" min="0" class="form-control @error('referral_bonus') is-invalid @enderror" value="{{ old('referral_bonus', $settings['referral_bonus']) }}" required>
                    @error('referral_bonus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold"><i class="bi bi-cone-striped me-2 text-warning"></i>{{ __('Maintenance') }}</div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="maintenance" id="maintenance" value="1" @checked(old('maintenance', $settings['maintenance']) === '1')>
                    <label class="form-check-label" for="maintenance">
                        {{ __('Activer le mode maintenance (seuls les administrateurs peuvent naviguer)') }}
                    </label>
                </div>
            </div>
        </div>

        <button class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>{{ __('Enregistrer les paramètres') }}</button>
    </form>
</div>
@endsection
