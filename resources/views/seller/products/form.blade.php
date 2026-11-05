@extends('layouts.app')

@section('title', ($product->exists ? __('Modifier le produit') : __('Ajouter un produit')).' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4" style="max-width: 760px;">
    <h1 class="h3 fw-bold mb-4">
        <i class="bi {{ $product->exists ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2 text-primary"></i>
        {{ $product->exists ? __('Modifier le produit') : __('Ajouter un produit') }}
    </h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}"
                  method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                @if ($product->exists) @method('PUT') @endif

                <div class="col-md-8">
                    <label class="form-label">{{ __('Nom du produit') }} *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required maxlength="150">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Catégorie') }} *</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">—</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ __('Prix (FCFA)') }} *</label>
                    <input type="number" name="price" min="100" step="50" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $product->price) }}" required>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Ancien prix (promo, optionnel)') }}</label>
                    <input type="number" name="old_price" min="100" step="50" class="form-control @error('old_price') is-invalid @enderror"
                           value="{{ old('old_price', $product->old_price) }}">
                    @error('old_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Stock') }} *</label>
                    <input type="number" name="stock" min="0" class="form-control @error('stock') is-invalid @enderror"
                           value="{{ old('stock', $product->stock ?? 1) }}" required>
                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                              placeholder="{{ __('Caractéristiques, garantie, tailles disponibles...') }}">{{ old('description', $product->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label">{{ __('Photo du produit') }}</label>
                    <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror"
                           onchange="MultiShop.previewImage(this, 'img-preview')">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="opacity-50">{{ __('JPG, PNG ou WebP — 2 Mo maximum.') }}</small>
                </div>
                <div class="col-md-4 text-center">
                    <img id="img-preview" src="{{ $product->exists ? $product->image_url : asset('images/categories/default.svg') }}"
                         class="ms-table-thumb-lg rounded shadow-sm" alt="">
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               @checked(old('is_active', $product->exists ? $product->is_active : true))>
                        <label class="form-check-label" for="is_active">{{ __('Produit en ligne (visible par les clients)') }}</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>{{ __('Enregistrer') }}</button>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
