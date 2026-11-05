@extends('layouts.app')

@section('title', __('Commandes de ma boutique').' — '.setting('site_name', 'MultiShop'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-receipt me-2 text-primary"></i>{{ __('Commandes de ma boutique') }}</h1>

    {{-- Onglets par statut --}}
    <ul class="nav nav-pills mb-4 flex-wrap gap-1">
        <li class="nav-item">
            <a class="nav-link @if(!$status) active @endif" href="{{ route('seller.orders.index') }}">
                {{ __('Toutes') }} <span class="badge text-bg-light ms-1">{{ $counts->sum() }}</span>
            </a>
        </li>
        @foreach (\App\Models\Order::STATUSES as $s)
            <li class="nav-item">
                <a class="nav-link @if($status === $s) active @endif" href="{{ route('seller.orders.index', ['statut' => $s]) }}">
                    {{ __('statut.'.$s) }} <span class="badge text-bg-light ms-1">{{ $counts[$s] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th>#</th><th>{{ __('Client') }}</th><th>{{ __('Articles') }}</th><th>{{ __('Total') }}</th>
                    <th>{{ __('Paiement') }}</th><th>{{ __('Date') }}</th><th>{{ __('Statut') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->id }}</td>
                        <td>
                            {{ $order->delivery_name }}<br>
                            <small class="opacity-75"><i class="bi bi-telephone me-1"></i>{{ $order->delivery_phone }}</small>
                        </td>
                        <td>{{ $order->items->sum('quantity') }}</td>
                        <td class="fw-semibold">{{ fcfa($order->total) }}</td>
                        <td>
                            <small>
                                {{ __('paiement.'.$order->payment_method) }}<br>
                                <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ __('paiement_statut.'.$order->payment_status) }}</span>
                            </small>
                        </td>
                        <td><small>{{ $order->created_at->translatedFormat('d M Y H:i') }}</small></td>
                        <td><span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span></td>
                        <td class="text-nowrap">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Détails') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 opacity-50">{{ __('Aucune commande pour le moment.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
