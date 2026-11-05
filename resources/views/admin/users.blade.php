@extends('layouts.app')

@section('title', __('Utilisateurs').' — '.__('Administration'))

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-people me-2 text-primary"></i>{{ __('Gestion des utilisateurs') }}</h1>

    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <ul class="nav nav-pills flex-wrap gap-1 me-auto">
            @foreach (['' => __('Tous'), 'customer' => __('role.customer'), 'seller' => __('role.seller'), 'admin' => __('role.admin')] as $r => $label)
                <li class="nav-item">
                    <a class="nav-link @if(($role ?? '') === $r || (!$role && $r === '')) active @endif"
                       href="{{ route('admin.users.index', $r ? ['role' => $r] : []) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
        <form method="GET" style="max-width: 300px;">
            @if ($role)<input type="hidden" name="role" value="{{ $role }}">@endif
            <div class="input-group input-group-sm">
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Nom, e-mail...') }}">
                <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small opacity-75">
                    <th>{{ __('Utilisateur') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Rôle') }}</th>
                    <th>{{ __('Commandes') }}</th><th>🏆 / 🎁</th><th>{{ __('Inscrit le') }}</th><th>{{ __('État') }}</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($users as $u)
                    <tr class="{{ $u->is_active ? '' : 'opacity-50' }}">
                        <td>
                            <strong>{{ $u->full_name }}</strong>
                            @if ($u->username)<br><small class="opacity-75">@ {{ $u->username }}</small>@endif
                        </td>
                        <td><small>{{ $u->email }}<br>{{ $u->phone }}</small></td>
                        <td>
                            <span class="badge text-bg-{{ ['customer' => 'secondary', 'seller' => 'info', 'admin' => 'dark'][$u->role] }}">
                                {{ __('role.'.$u->role) }}
                            </span>
                        </td>
                        <td>{{ $u->orders_count }}</td>
                        <td><small>{{ $u->loyalty_points }} pts · {{ fcfa($u->store_credit) }}</small></td>
                        <td><small>{{ $u->created_at->translatedFormat('d M Y') }}</small></td>
                        <td>
                            <span class="badge text-bg-{{ $u->is_active ? 'success' : 'danger' }}">
                                {{ $u->is_active ? __('Actif') : __('Désactivé') }}
                            </span>
                        </td>
                        <td>
                            @if ($u->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle', $u) }}" method="POST"
                                      onsubmit="return confirm('{{ __('Changer l\'état de ce compte ?') }}')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-{{ $u->is_active ? 'danger' : 'success' }}"
                                            title="{{ $u->is_active ? __('Désactiver') : __('Réactiver') }}">
                                        <i class="bi {{ $u->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                    </button>
                                </form>
                            @else
                                <small class="opacity-50">{{ __('(vous)') }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 opacity-50">{{ __('Aucun utilisateur trouvé.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
