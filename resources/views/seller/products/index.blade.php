@extends('layouts.app')

@section('title', __('Mes produits').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>{{ __('Mes produits') }}</h1>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter un produit') }}
        </a>
    </div>

    <form method="GET" class="mb-3" style="max-width: 380px;">
        <div class="input-group">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Rechercher...') }}">
            <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th></th><th>{{ __('Produit') }}</th><th>{{ __('Catégorie') }}</th><th>{{ __('Prix') }}</th>
                    <th>{{ __('Stock') }}</th><th>{{ __('Ventes') }}</th><th>{{ __('État') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><img src="{{ $product->image_url }}" class="ms-table-thumb rounded" alt=""></td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td><small class="opacity-75">{{ $product->category?->name ?? '—' }}</small></td>
                        <td>{{ fcfa($product->price) }}</td>
                        <td>
                            <span class="badge text-bg-{{ $product->stock === 0 ? 'danger' : ($product->stock <= 3 ? 'warning' : 'success') }}">{{ $product->stock }}</span>
                        </td>
                        <td>{{ $product->sales_count }}</td>
                        <td>
                            @unless ($product->is_visible)
                                <span class="badge text-bg-danger" title="{{ __('Masqué par la modération') }}"><i class="bi bi-eye-slash"></i> {{ __('Modéré') }}</span>
                            @endunless
                            <span class="badge text-bg-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? __('En ligne') : __('Désactivé') }}</span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Supprimer ce produit ?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 opacity-50">{{ __('Aucun produit. Ajoutez votre premier article !') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
</div>
@endsection
