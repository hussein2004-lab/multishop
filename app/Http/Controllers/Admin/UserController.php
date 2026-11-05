<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /** Gestion des utilisateurs. */
    public function index(Request $request)
    {
        $role = $request->query('role');
        $q    = trim((string) $request->query('q'));

        $users = User::query()
            ->withCount('orders')
            ->when(in_array($role, ['customer', 'seller', 'admin']), fn ($query) => $query->where('role', $role))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('first_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%")))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users', compact('users', 'role', 'q'));
    }

    /** Activer / désactiver un compte. */
    public function toggleActive(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', __('Vous ne pouvez pas désactiver votre propre compte.'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? __('Compte de :name réactivé.', ['name' => $user->full_name])
            : __('Compte de :name désactivé.', ['name' => $user->full_name]));
    }
}
