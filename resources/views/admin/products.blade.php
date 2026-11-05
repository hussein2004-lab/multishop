@extends('layouts.app')

@section('title', __('Modération des produits').' — '.__('Administration'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-shield-check me-2 text-primary"></i>{{ __('Modération des produits') }}</h1>

    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <ul class="nav nav-pills gap-1 me-auto">
            <li class="nav-item"><a class="nav-link @if(!$filter) active @endif" href="{{ route('admin.products.index') }}">{{ __('Tous') }}</a></li>
            <li class="nav-item"><a class="nav-link @if($filter === 'hidden') active @endif" href="{{ route('admin.products.index', ['filtre' => 'hidden']) }}"><i class="bi bi-eye-slash me-1"></i>{{ __('Masqués') }}</a></li>
        </ul>
        <form method="GET" style="max-width: 300px;">
            @if ($filter)<input type="hidden" name="filtre" value="{{ $filter }}">@endif
            <div class="input-group input-group-sm">
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Nom du produit...') }}">
                <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th></th><th>{{ __('Produit') }}</th><th>{{ __('Boutique') }}</th><th>{{ __('Prix') }}</th>
                    <th>{{ __('Stock') }}</th><th>{{ __('État') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr class="{{ $product->is_visible ? '' : 'table-warning' }}">
                        <td><img src="{{ $product->image_url }}" class="ms-table-thumb rounded" alt=""></td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" target="_blank" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                            <br><small class="opacity-75">{{ $product->category?->name }}</small>
                        </td>
                        <td><small>{{ $product->shop->name }}</small></td>
                        <td>{{ fcfa($product->price) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            <span class="badge text-bg-{{ $product->is_visible ? 'success' : 'danger' }}">
                                {{ $product->is_visible ? __('Visible') : __('Masqué') }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <form action="{{ route('admin.products.toggle', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $product->is_visible ? 'warning' : 'success' }}"
                                        title="{{ $product->is_visible ? __('Masquer (modération)') : __('Réafficher') }}">
                                    <i class="bi {{ $product->is_visible ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Supprimer définitivement ce produit ?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 opacity-50">{{ __('Aucun produit trouvé.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
</div>
@endsection
