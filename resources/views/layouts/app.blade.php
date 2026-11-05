<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', 'MultiShop').' — '.__(setting('site_tagline', 'Tout ce dont vous avez besoin')))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @if (locale_dir() === 'rtl')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body data-theme="{{ current_theme() }}" @if(current_theme() === 'dark') data-bs-theme="dark" @endif>

{{-- ============================== NAVBAR ============================== --}}
<nav class="navbar navbar-expand-lg ms-navbar sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name', 'MultiShop') }}" class="ms-logo">
            <span class="fw-bold ms-brand-text">Multi<span class="text-primary">Shop</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">{{ __('Accueil') }}</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('shops.*')) active @endif" href="{{ route('shops.index') }}">{{ __('Boutiques') }}</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('products.*')) active @endif" href="{{ route('products.index') }}">{{ __('Produits') }}</a></li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('assistant')) active @endif" href="{{ route('assistant') }}">
                        <i class="bi bi-stars text-warning"></i> {{ __('Assistant IA') }}
                    </a>
                </li>
            </ul>

            {{-- Recherche IA rapide --}}
            <form class="d-flex me-lg-3 my-2 my-lg-0 ms-ai-search" action="{{ route('assistant') }}" method="GET" role="search">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-magic"></i></span>
                    <input class="form-control" type="search" name="q" placeholder="{{ __('Dites-moi ce que vous cherchez...') }}" value="{{ request('q') }}">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <ul class="navbar-nav align-items-lg-center gap-lg-1">
                {{-- 🎨 Thème --}}
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" data-bs-toggle="dropdown" title="{{ __('Thème') }}">
                        <i class="bi {{ ['light' => 'bi-sun-fill', 'dark' => 'bi-moon-stars-fill', 'sepia' => 'bi-cup-hot-fill', 'gray' => 'bi-circle-half'][current_theme()] }}"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-2">
                        <form action="{{ route('preferences.theme') }}" method="POST">
                            @csrf
                            @foreach ([
                                'light' => ['bi-sun-fill', __('Clair')],
                                'dark'  => ['bi-moon-stars-fill', __('Sombre')],
                                'sepia' => ['bi-cup-hot-fill', __('Sépia')],
                                'gray'  => ['bi-circle-half', __('Gris clair')],
                            ] as $themeKey => [$icon, $label])
                                <button type="submit" name="theme" value="{{ $themeKey }}"
                                        class="dropdown-item rounded d-flex align-items-center gap-2 @if(current_theme() === $themeKey) active @endif">
                                    <span class="ms-theme-dot ms-theme-dot-{{ $themeKey }}"></span>
                                    <i class="bi {{ $icon }} small"></i> {{ $label }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </li>

                {{-- 🌍 Langue --}}
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" data-bs-toggle="dropdown" title="{{ __('Langue') }}">
                        {{ config('app.available_locales.'.app()->getLocale().'.flag', '🌍') }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 11rem;">
                        <form action="{{ route('preferences.locale') }}" method="POST">
                            @csrf
                            @foreach (config('app.available_locales') as $code => $loc)
                                <button type="submit" name="locale" value="{{ $code }}"
                                        class="dropdown-item rounded @if(app()->getLocale() === $code) active @endif">
                                    {{ $loc['flag'] }} {{ $loc['native'] }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </li>

                {{-- 🛒 Panier --}}
                <li class="nav-item">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="bi bi-cart3 fs-5"></i>
                        @php $cartCount = \App\Services\CartService::count(); @endphp
                        @if ($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger ms-cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>

                {{-- 👤 Compte --}}
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <span class="ms-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->first_name ?: auth()->user()->name, 0, 1)) }}</span>
                            <span class="d-lg-none">{{ auth()->user()->first_name ?: auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ auth()->user()->full_name }}</h6></li>
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>{{ __('Administration') }}</a></li>
                            @endif
                            @if (auth()->user()->isSeller())
                                <li><a class="dropdown-item" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop me-2"></i>{{ __('Ma boutique') }}</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-box-seam me-2"></i>{{ __('Mes commandes') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('account.index') }}"><i class="bi bi-person-gear me-2"></i>{{ __('Mon compte') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Déconnexion') }}</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Connexion') }}</a></li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3 ms-lg-1" href="{{ route('register') }}">{{ __('Inscription') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ============================== FLASH ============================== --}}
<div class="container mt-3 ms-flash">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

{{-- ============================== CONTENU ============================== --}}
<main class="ms-main pb-5">
    @yield('content')
</main>

{{-- ============================== FOOTER ============================== --}}
<footer class="ms-footer pt-5 pb-4 mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/logo.png') }}" alt="logo" class="ms-logo">
                    <span class="fw-bold fs-5">Multi<span class="text-primary">Shop</span></span>
                </div>
                <p class="small opacity-75 mb-2">{{ __(setting('site_tagline', 'Tout ce dont vous avez besoin')) }}.
                    {{ __('La marketplace qui connecte les boutiques locales de Côte d\'Ivoire à leurs clients.') }}</p>
                <p class="small opacity-75 mb-0">
                    <i class="bi bi-envelope me-1"></i>{{ setting('contact_email', 'contact@multishop.ci') }}<br>
                    <i class="bi bi-telephone me-1"></i>{{ setting('contact_phone', '+225 07 00 00 00 00') }}
                </p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3">{{ __('Navigation') }}</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('home') }}">{{ __('Accueil') }}</a></li>
                    <li class="mb-2"><a href="{{ route('shops.index') }}">{{ __('Boutiques') }}</a></li>
                    <li class="mb-2"><a href="{{ route('products.index') }}">{{ __('Produits') }}</a></li>
                    <li class="mb-2"><a href="{{ route('assistant') }}">{{ __('Assistant IA') }}</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="fw-bold mb-3">{{ __('Catégories') }}</h6>
                <ul class="list-unstyled small">
                    @foreach (($navCategories ?? collect())->take(6) as $cat)
                        <li class="mb-2">
                            <a href="{{ route('products.index', ['categorie' => $cat->slug]) }}">{{ $cat->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="fw-bold mb-3">{{ __('Vous êtes commerçant ?') }}</h6>
                <p class="small opacity-75">{{ __('Inscrivez votre boutique et vendez à toute la ville.') }}</p>
                <a href="{{ route('register.seller') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-shop me-1"></i>{{ __('Ouvrir ma boutique') }}
                </a>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <p class="text-center small opacity-50 mb-0">© {{ date('Y') }} {{ setting('site_name', 'MultiShop') }} — {{ __('Tous droits réservés') }}.</p>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
