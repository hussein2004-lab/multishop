@php
    $waText = rawurlencode('🛍️ '.$product->name.' — '.fcfa($product->price).' '.__('sur').' '.setting('site_name', 'MultiShop').' : '.route('products.show', $product));
@endphp
<div class="card h-100 ms-product-card anim-card shadow-sm border-0">
    <a href="{{ route('products.show', $product) }}" class="position-relative d-block ms-product-img-wrap">
        <img src="{{ $product->image_url }}" class="card-img-top ms-product-img" alt="{{ $product->name }}" loading="lazy">
        @if ($product->discount_percent)
            <span class="badge bg-danger position-absolute top-0 start-0 m-2">-{{ $product->discount_percent }}%</span>
        @endif
        @if ($product->stock < 1)
            <span class="badge bg-dark position-absolute top-0 end-0 m-2">{{ __('Rupture') }}</span>
        @endif
    </a>
    <div class="card-body d-flex flex-column pb-2">
        <div class="small mb-1 d-flex justify-content-between align-items-center gap-1">
            <a href="{{ route('shops.show', $product->shop) }}" class="text-decoration-none text-truncate ms-shop-link">
                <i class="bi bi-shop me-1"></i>{{ $product->shop->name }}
            </a>
            @include('partials.reliability-badge', ['shop' => $product->shop])
        </div>
        <a href="{{ route('products.show', $product) }}" class="text-decoration-none ms-product-title">
            <h6 class="card-title mb-1 text-truncate-2">{{ $product->name }}</h6>
        </a>
        <div class="mt-auto">
            <span class="fw-bold text-primary">{{ fcfa($product->price) }}</span>
            @if ($product->old_price)
                <small class="text-decoration-line-through opacity-50 ms-1">{{ fcfa($product->old_price) }}</small>
            @endif
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 pt-0 pb-3 d-flex gap-2">
        <form action="{{ route('cart.add', $product) }}" method="POST" class="flex-grow-1">
            @csrf
            <button class="btn btn-primary btn-sm w-100" @disabled($product->stock < 1)>
                <i class="bi bi-cart-plus me-1"></i>{{ __('Ajouter au panier') }}
            </button>
        </form>
        <a class="btn btn-success btn-sm ms-wa-btn" target="_blank" rel="noopener"
           href="https://wa.me/?text={{ $waText }}" title="{{ __('Partager sur WhatsApp') }}">
            <i class="bi bi-whatsapp"></i>
        </a>
    </div>
</div>
