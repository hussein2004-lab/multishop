<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /** Mon compte : profil, fidélité, parrainage, crédit. */
    public function index(Request $request)
    {
        $user = $request->user();

        return view('account.index', [
            'user'         => $user,
            'loyaltyLog'   => $user->loyaltyTransactions()->take(10)->get(),
            'creditLog'    => $user->creditTransactions()->take(10)->get(),
            'referrals'    => $user->referrals()->latest()->take(10)->get(),
            'pointValue'   => (int) setting('loyalty_point_value', 10),
            'referralLink' => route('register', ['ref' => $user->referral_code]),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'username'   => ['nullable', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email'      => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user->id)],
            'phone'      => 'nullable|string|max:30',
            'password'   => 'nullable|string|min:6|confirmed',
            'current_password' => 'required_with:password|nullable|current_password',
        ]);

        $user->fill([
            'name'       => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'username'   => $data['username'] ?? null,
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('success', __('Profil mis à jour.'));
    }
}
