@extends('layouts.app')

@section('title', __('Boutiques').' — '.__('Administration'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-shop me-2 text-primary"></i>{{ __('Gestion des boutiques') }}</h1>

    <ul class="nav nav-pills mb-4 flex-wrap gap-1">
        @foreach (['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'all' => 'secondary'] as $s => $color)
            <li class="nav-item">
                <a class="nav-link @if($status === $s) active @endif" href="{{ route('admin.shops.index', ['statut' => $s]) }}">
                    {{ $s === 'all' ? __('Toutes') : __('boutique_statut.'.$s) }}
                    <span class="badge text-bg-{{ $color }} ms-1">{{ $s === 'all' ? $counts->sum() : ($counts[$s] ?? 0) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th>{{ __('Boutique') }}</th><th>{{ __('Vendeur') }}</th><th>{{ __('Ville') }}</th>
                    <th>{{ __('Produits') }}</th><th>{{ __('Fiabilité') }}</th><th>{{ __('Date') }}</th><th>{{ __('Statut') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($shops as $shop)
                    <tr>
                        <td>
                            <strong>{{ $shop->name }}</strong>
                            @if ($shop->status === 'rejected' && $shop->rejection_reason)
                                <br><small class="text-danger">{{ __('Motif') }} : {{ $shop->rejection_reason }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $shop->user->full_name }}<br>
                            <small class="opacity-75">{{ $shop->user->email }} · {{ $shop->phone }}</small>
                        </td>
                        <td>{{ $shop->city }}</td>
                        <td>{{ $shop->products_count }}</td>
                        <td>@include('partials.reliability-badge', ['shop' => $shop])</td>
                        <td><small>{{ $shop->created_at->translatedFormat('d M Y') }}</small></td>
                        <td>
                            <span class="badge text-bg-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$shop->status] }}">
                                {{ __('boutique_statut.'.$shop->status) }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            @if ($shop->status !== 'approved')
                                <form action="{{ route('admin.shops.approve', $shop) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm" title="{{ __('Approuver') }}"><i class="bi bi-check-lg"></i></button>
                                </form>
                            @endif
                            @if ($shop->status !== 'rejected')
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reject-{{ $shop->id }}" title="{{ __('Refuser') }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                {{-- Modal de refus --}}
                                <div class="modal fade" id="reject-{{ $shop->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.shops.reject', $shop) }}" method="POST" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Refuser') }} « {{ $shop->name }} »</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <label class="form-label small">{{ __('Motif (visible par le vendeur)') }}</label>
                                                <textarea name="reason" rows="3" class="form-control" placeholder="{{ __('Ex : informations incomplètes...') }}"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                                <button class="btn btn-danger">{{ __('Refuser la boutique') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                            @if ($shop->status === 'approved')
                                <a href="{{ route('shops.show', $shop) }}" target="_blank" class="btn btn-outline-primary btn-sm" title="{{ __('Voir') }}"><i class="bi bi-eye"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 opacity-50">{{ __('Aucune boutique dans cette catégorie.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $shops->links() }}</div>
</div>
@endsection
