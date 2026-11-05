<div class="card h-100 ms-shop-card anim-card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
            <span class="ms-shop-avatar">{{ mb_strtoupper(mb_substr($shop->name, 0, 1)) }}</span>
            <div class="flex-grow-1 min-w-0">
                <h5 class="card-title mb-0 text-truncate">{{ $shop->name }}</h5>
                <small class="opacity-75"><i class="bi bi-geo-alt me-1"></i>{{ $shop->city ?: $shop->address }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            @include('partials.reliability-badge', ['shop' => $shop])
            @if ($shop->rating_count > 0)
                @include('partials.stars', ['rating' => $shop->rating_avg])
                <small class="opacity-75">({{ $shop->rating_count }})</small>
            @endif
        </div>
        <p class="card-text small opacity-75 text-truncate-2 mb-2">{{ $shop->description }}</p>
        <small class="opacity-75"><i class="bi bi-box-seam me-1"></i>{{ $shop->products_count ?? $shop->products()->count() }} {{ __('produits') }}</small>
    </div>
    <div class="card-footer bg-transparent border-0 pb-3">
        <a href="{{ route('shops.show', $shop) }}" class="btn btn-outline-primary btn-sm w-100">
            <i class="bi bi-door-open me-1"></i>{{ __('Visiter la boutique') }}
        </a>
    </div>
</div>
