@extends('layouts.app')

@section('title', __('Commander').' — '.setting('site_name', 'MultiShop'))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-bag-check me-2 text-primary"></i>{{ __('Finaliser ma commande') }}</h1>

    <form action="{{ route('checkout.place') }}" method="POST" class="row g-4">
        @csrf
        <div class="col-lg-8">

            {{-- Livraison --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-truck me-2 text-primary"></i>{{ __('Informations de livraison') }}</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Nom complet') }} *</label>
                        <input type="text" name="delivery_name" class="form-control @error('delivery_name') is-invalid @enderror"
                               value="{{ old('delivery_name', auth()->user()->full_name) }}" required>
                        @error('delivery_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Téléphone') }} *</label>
                        <input type="tel" name="delivery_phone" class="form-control @error('delivery_phone') is-invalid @enderror"
                               value="{{ old('delivery_phone', auth()->user()->phone) }}" required placeholder="+225 ...">
                        @error('delivery_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Adresse de livraison') }} *</label>
                        <textarea name="delivery_address" rows="2" class="form-control @error('delivery_address') is-invalid @enderror"
                                  required placeholder="{{ __('Quartier, rue, repère...') }}">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Carte --}}
                    <div class="col-12">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ __('Votre position sur la carte') }}</span>
                            <button type="button" id="btn-locate" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-crosshair me-1"></i>{{ __('Ma position actuelle') }}
                            </button>
                        </label>
                        <div id="delivery-map" class="ms-map rounded shadow-sm"></div>
                        <small class="opacity-75">{{ __('Cliquez sur la carte pour placer le point de livraison.') }}</small>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ __('Note pour le livreur (optionnel)') }}</label>
                        <input type="text" name="note" class="form-control" value="{{ old('note') }}"
                               placeholder="{{ __('Ex : appeler en arrivant') }}">
                    </div>
                </div>
            </div>

            {{-- Paiement --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-credit-card me-2 text-primary"></i>{{ __('Mode de paiement') }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ([
                            'card'   => ['bi-credit-card-2-front-fill', __('Carte Visa / bancaire'), __('Paiement sécurisé avec vérification par code SMS')],
                            'paypal' => ['bi-paypal', 'PayPal', __('Connectez votre compte PayPal, vérification par code SMS')],
                            'cash'   => ['bi-cash-coin', __('Espèces à la livraison'), __('Payez quand vous recevez votre article')],
                        ] as $value => [$icon, $label, $hint])
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="payment_method" id="pm-{{ $value }}"
                                       value="{{ $value }}" @checked(old('payment_method', 'cash') === $value) required>
                                <label class="btn btn-outline-primary w-100 h-100 text-start p-3 ms-pay-option" for="pm-{{ $value }}">
                                    <i class="bi {{ $icon }} fs-3 d-block mb-2"></i>
                                    <span class="fw-semibold d-block">{{ $label }}</span>
                                    <small class="opacity-75">{{ $hint }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('payment_method')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Résumé --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm ms-summary-card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">{{ __('Résumé') }}</h5>

                    @foreach ($byShop as $shopItems)
                        <div class="small mb-2">
                            <div class="fw-semibold"><i class="bi bi-shop me-1 text-primary"></i>{{ $shopItems->first()['product']->shop->name }}</div>
                            @foreach ($shopItems as $item)
                                <div class="d-flex justify-content-between opacity-75">
                                    <span class="text-truncate me-2">{{ $item['qty'] }} × {{ $item['product']->name }}</span>
                                    <span class="text-nowrap">{{ fcfa($item['line']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span class="opacity-75">{{ __('Sous-total') }}</span><span>{{ fcfa($subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="opacity-75">{{ __('Livraison') }}</span><span>{{ fcfa($deliveryFee) }}</span>
                    </div>

                    {{-- 🎁 Avantages --}}
                    @if ($creditValue > 0)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="use_credit" id="use_credit" value="1" @checked(old('use_credit'))>
                            <label class="form-check-label small" for="use_credit">
                                🎁 {{ __('Utiliser mon crédit parrainage') }} (<strong>{{ fcfa($creditValue) }}</strong>)
                            </label>
                        </div>
                    @endif
                    @if ($pointsValue > 0)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="use_points" id="use_points" value="1" @checked(old('use_points'))>
                            <label class="form-check-label small" for="use_points">
                                🏆 {{ __('Utiliser mes points fidélité') }} (<strong>{{ fcfa($pointsValue) }}</strong>)
                            </label>
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>{{ __('Total') }}</span>
                        <span class="text-primary">{{ fcfa($total) }}</span>
                    </div>
                    <button class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-lock-fill me-1"></i>{{ __('Confirmer la commande') }}
                    </button>
                    <small class="d-block text-center mt-2 opacity-50">{{ __('Les remises sont appliquées à la validation.') }}</small>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            MultiShop.initDeliveryMap('delivery-map', 'latitude', 'longitude', 'btn-locate');
        });
    </script>
@endpush
