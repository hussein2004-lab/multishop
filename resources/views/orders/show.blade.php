@extends('layouts.app')

@section('title', __('Commande').' #'.$order->id.' — '.setting('site_name', 'MultiShop'))

@push('styles')
    @if ($order->latitude && $order->longitude)
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    @endif
@endpush

@php
    $isCustomer = auth()->id() === $order->user_id;
    $isSeller   = auth()->user()->isSeller() && auth()->user()->shop && auth()->user()->shop->id === $order->shop_id;
    $isAdmin    = auth()->user()->isAdmin();
    $steps      = ['pending', 'confirmed', 'shipped', 'delivered'];
    $stepIndex  = array_search($order->status, $steps);
@endphp

@section('content')
<div class="container py-4" style="max-width: 900px;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h1 class="h3 fw-bold mb-0">{{ __('Commande') }} #{{ $order->id }}</h1>
        <span class="badge text-bg-{{ $order->status_color }} fs-6">{{ __('statut.'.$order->status) }}</span>
    </div>

    {{-- Progression --}}
    @if ($order->status !== 'cancelled')
        <div class="ms-timeline mb-4">
            @foreach ([
                ['bi-hourglass-split', __('statut.pending')],
                ['bi-check2-circle', __('statut.confirmed')],
                ['bi-truck', __('statut.shipped')],
                ['bi-house-check', __('statut.delivered')],
            ] as $i => [$icon, $label])
                <div class="ms-timeline-step {{ $stepIndex !== false && $i <= $stepIndex ? 'done' : '' }}">
                    <div class="ms-timeline-dot"><i class="bi {{ $icon }}"></i></div>
                    <small>{{ $label }}</small>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>{{ __('Cette commande a été annulée.') }}
            @if ($order->discount > 0) {{ __('La remise utilisée a été remboursée en crédit.') }} @endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Articles --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-shop me-1 text-primary"></i>
                    <a href="{{ route('shops.show', $order->shop) }}" class="text-decoration-none">{{ $order->shop->name }}</a>
                </div>
                <div class="card-body p-0">
                    @foreach ($order->items as $item)
                        <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                            @if ($item->product)
                                <img src="{{ $item->product->image_url }}" class="ms-cart-thumb rounded" alt="">
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                <small class="opacity-75">{{ $item->quantity }} × {{ fcfa($item->unit_price) }}</small>
                            </div>
                            <strong>{{ fcfa($item->line_total) }}</strong>
                        </div>
                    @endforeach
                    <div class="p-3">
                        <div class="d-flex justify-content-between small mb-1"><span class="opacity-75">{{ __('Sous-total') }}</span><span>{{ fcfa($order->subtotal) }}</span></div>
                        <div class="d-flex justify-content-between small mb-1"><span class="opacity-75">{{ __('Livraison') }}</span><span>{{ fcfa($order->delivery_fee) }}</span></div>
                        @if ($order->discount > 0)
                            <div class="d-flex justify-content-between small mb-1 text-success"><span>{{ __('Remise (points + crédit)') }}</span><span>-{{ fcfa($order->discount) }}</span></div>
                        @endif
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>{{ __('Total') }}</span><span class="text-primary">{{ fcfa($order->total) }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Paiement --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        @if ($order->payment_method === 'cash') <i class="bi bi-cash-coin me-1"></i> @elseif($order->payment_method === 'paypal') <i class="bi bi-paypal me-1"></i> @else <i class="bi bi-credit-card me-1"></i> @endif
                        {{ __('paiement.'.$order->payment_method) }}
                    </span>
                    <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                        {{ __('paiement_statut.'.$order->payment_status) }}
                        @if ($order->paid_at) · {{ $order->paid_at->translatedFormat('d M Y H:i') }} @endif
                    </span>
                    @if ($isCustomer && $order->isAwaitingOnlinePayment())
                        <a href="{{ route('payment.show', $order->group_code) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-credit-card me-1"></i>{{ __('Terminer le paiement') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Actions client --}}
            @if ($isCustomer && $order->canBeCancelled())
                <form action="{{ route('orders.cancel', $order) }}" method="POST"
                      onsubmit="return confirm('{{ __('Annuler cette commande ?') }}')">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm mb-4"><i class="bi bi-x-circle me-1"></i>{{ __('Annuler la commande') }}</button>
                </form>
            @endif

            {{-- Avis --}}
            @if ($isCustomer && $order->status === 'delivered' && ! $order->review)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent fw-semibold"><i class="bi bi-star me-1 text-warning"></i>{{ __('Notez cette boutique') }}</div>
                    <div class="card-body">
                        <form action="{{ route('orders.review', $order) }}" method="POST">
                            @csrf
                            <div class="ms-rating mb-3">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" required>
                                    <label for="star{{ $i }}" title="{{ $i }}/5"><i class="bi bi-star-fill"></i></label>
                                @endfor
                            </div>
                            <textarea name="comment" rows="2" class="form-control mb-2" placeholder="{{ __('Votre commentaire (optionnel)') }}"></textarea>
                            <button class="btn btn-primary btn-sm">{{ __('Envoyer mon avis') }}</button>
                        </form>
                    </div>
                </div>
            @elseif ($order->review)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small">{{ __('Avis du client') }}</strong>
                            @include('partials.stars', ['rating' => $order->review->rating])
                        </div>
                        @if ($order->review->comment)<p class="small opacity-75 mb-0 mt-1">{{ $order->review->comment }}</p>@endif
                    </div>
                </div>
            @endif

            {{-- Actions vendeur --}}
            @if ($isSeller && in_array($order->status, ['pending', 'confirmed', 'shipped']))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent fw-semibold">{{ __('Mettre à jour le statut') }}</div>
                    <div class="card-body d-flex gap-2 flex-wrap">
                        <form action="{{ route('seller.orders.status', $order) }}" method="POST" class="d-flex gap-2 flex-wrap">
                            @csrf
                            @if ($order->status === 'pending')
                                <button name="status" value="confirmed" class="btn btn-info btn-sm"><i class="bi bi-check2-circle me-1"></i>{{ __('Confirmer') }}</button>
                            @endif
                            @if ($order->status === 'confirmed')
                                <button name="status" value="shipped" class="btn btn-primary btn-sm"><i class="bi bi-truck me-1"></i>{{ __('Expédier') }}</button>
                            @endif
                            @if ($order->status === 'shipped')
                                <button name="status" value="delivered" class="btn btn-success btn-sm"><i class="bi bi-house-check me-1"></i>{{ __('Marquer livrée') }}</button>
                            @endif
                            @if (in_array($order->status, ['pending', 'confirmed']))
                                <button name="status" value="cancelled" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('{{ __('Annuler cette commande ?') }}')">{{ __('Annuler') }}</button>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Livraison --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-geo-alt me-1 text-danger"></i>{{ __('Livraison') }}</div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->delivery_name }}</strong></p>
                    <p class="mb-1"><i class="bi bi-telephone me-1"></i>{{ $order->delivery_phone }}</p>
                    <p class="mb-2 opacity-75">{{ $order->delivery_address }}</p>
                    @if ($order->note)
                        <p class="small opacity-75"><i class="bi bi-chat-left-text me-1"></i>{{ $order->note }}</p>
                    @endif

                    @if ($order->latitude && $order->longitude)
                        <div id="order-map" class="ms-map-sm rounded shadow-sm mb-2"></div>
                        <a href="{{ $order->maps_link }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-google me-1"></i>{{ __('Ouvrir dans Google Maps') }}
                        </a>
                    @endif

                    @if ($isAdmin || $isSeller)
                        <hr>
                        <p class="small mb-0 opacity-75">
                            <i class="bi bi-person me-1"></i>{{ __('Client') }} : {{ $order->user->full_name }}<br>
                            <i class="bi bi-envelope me-1"></i>{{ $order->user->email }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if ($order->latitude && $order->longitude)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                MultiShop.initViewMap('order-map', {{ $order->latitude }}, {{ $order->longitude }});
            });
        </script>
    @endif
@endpush
