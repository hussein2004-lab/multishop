<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /** Liste des administrateurs + formulaire d'ajout. */
    public function index()
    {
        $admins = User::where('role', 'admin')->latest()->get();

        return view('admin.admins', compact('admins'));
    }

    /**
     * Ajout d'un administrateur : nom, prénom, email, contact,
     * nom d'utilisateur et mot de passe (vérifié par confirmation).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'email'      => 'required|email|max:190|unique:users,email',
            'phone'      => 'required|string|max:30',
            'username'   => 'required|string|min:3|max:30|alpha_dash|unique:users,username',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name'          => $data['name'],
            'first_name'    => $data['first_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'username'      => $data['username'],
            'password'      => $data['password'],
            'role'          => 'admin',
            'referral_code' => User::generateReferralCode(),
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', __('Administrateur :name ajouté.', ['name' => $data['first_name'].' '.$data['name']]));
    }
}
