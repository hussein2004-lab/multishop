@extends('layouts.app')

@section('title', __('Administrateurs').' — '.__('Administration'))

@section('content')
<div class="container py-4" style="max-width: 1000px;">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-person-badge me-2 text-primary"></i>{{ __('Administrateurs') }}</h1>

    <div class="row g-4">
        {{-- Liste --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">{{ __('Administrateurs actuels') }}</div>
                <ul class="list-group list-group-flush">
                    @foreach ($admins as $admin)
                        <li class="list-group-item bg-transparent d-flex align-items-center gap-3">
                            <span class="ms-avatar">{{ mb_strtoupper(mb_substr($admin->first_name ?: $admin->name, 0, 1)) }}</span>
                            <div class="flex-grow-1">
                                <strong>{{ $admin->full_name }}</strong>
                                @if ($admin->id === auth()->id())<small class="opacity-50">{{ __('(vous)') }}</small>@endif
                                <br><small class="opacity-75">
                                    @ {{ $admin->username }} · {{ $admin->email }}@if($admin->phone) · {{ $admin->phone }}@endif
                                </small>
                            </div>
                            <span class="badge text-bg-{{ $admin->is_active ? 'success' : 'danger' }}">
                                {{ $admin->is_active ? __('Actif') : __('Désactivé') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Ajout --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-person-plus me-2 text-success"></i>{{ __('Ajouter un administrateur') }}</div>
                <div class="card-body">
                    <form action="{{ route('admin.admins.store') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Nom') }} *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Prénom') }} *</label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('E-mail') }} *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Contact (téléphone)') }} *</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required placeholder="+225 ...">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small">{{ __('Nom d\'utilisateur') }} *</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Mot de passe') }} *</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">{{ __('Confirmer le mot de passe') }} *</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success w-100"><i class="bi bi-person-plus me-1"></i>{{ __('Créer l\'administrateur') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
