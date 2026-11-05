<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_name', 'MultiShop') }} — {{ __('Maintenance') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body data-theme="light" class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="text-center p-4">
        <img src="{{ asset('images/logo.png') }}" alt="logo" style="height: 90px;" class="mb-4">
        <h1 class="h3 mb-3">{{ __('Site en maintenance') }}</h1>
        <p class="opacity-75">{{ __('Nous revenons très vite. Merci de votre patience !') }}</p>
        <a href="{{ route('login') }}" class="small opacity-50">{{ __('Connexion administrateur') }}</a>
    </div>
</body>
</html>
