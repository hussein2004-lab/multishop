@extends('layouts.app')

@section('title', __('Commandes').' — '.__('Administration'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>{{ __('Toutes les commandes') }}</h1>
        <div class="d-flex gap-3">
            <span class="badge text-bg-light border fs-6">{{ $totals['count'] }} {{ __('commandes') }}</span>
            <span class="badge text-bg-success fs-6">{{ fcfa($totals['revenue']) }} {{ __('encaissés') }}</span>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-4" style="max-width: 600px;">
        <div class="col-sm-5">
            <select name="statut" class="form-select form-select-sm">
                <option value="">{{ __('Tous les statuts') }}</option>
                @foreach (\App\Models\Order::STATUSES as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ __('statut.'.$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4">
            <select name="paiement" class="form-select form-select-sm">
                <option value="">{{ __('Tous les paiements') }}</option>
                @foreach (['card', 'paypal', 'cash'] as $m)
                    <option value="{{ $m }}" @selected($payment === $m)>{{ __('paiement.'.$m) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <button class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>{{ __('Filtrer') }}</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th>#</th><th>{{ __('Client') }}</th><th>{{ __('Boutique') }}</th><th>{{ __('Total') }}</th>
                    <th>{{ __('Paiement') }}</th><th>{{ __('Statut') }}</th><th>{{ __('Date') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->id }}</td>
                        <td>{{ $order->user->full_name }}<br><small class="opacity-75">{{ $order->delivery_phone }}</small></td>
                        <td><small>{{ $order->shop->name }}</small></td>
                        <td class="fw-semibold">{{ fcfa($order->total) }}</td>
                        <td>
                            <small>{{ __('paiement.'.$order->payment_method) }}<br>
                            <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ __('paiement_statut.'.$order->payment_status) }}</span></small>
                        </td>
                        <td><span class="badge text-bg-{{ $order->status_color }}">{{ __('statut.'.$order->status) }}</span></td>
                        <td><small>{{ $order->created_at->translatedFormat('d M Y H:i') }}</small></td>
                        <td><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 opacity-50">{{ __('Aucune commande trouvée.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
