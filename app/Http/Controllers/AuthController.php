<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ------------------------------------------------------------ Connexion

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Connexion par e-mail OU nom d'utilisateur
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => __('Identifiants incorrects.')])
                ->onlyInput('login');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['login' => __('Votre compte a été désactivé.')])->onlyInput('login');
        }

        // Applique les préférences enregistrées (thème + langue)
        session(['theme' => $user->theme, 'locale' => $user->locale]);

        return match (true) {
            $user->isAdmin()  => redirect()->intended(route('admin.dashboard')),
            $user->isSeller() => redirect()->intended(route('seller.dashboard')),
            default           => redirect()->intended(route('home')),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // ------------------------------------------------------------ Inscription client

    public function showRegister(Request $request)
    {
        return view('auth.register', ['ref' => $request->query('ref')]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'first_name'    => 'required|string|max:100',
            'username'      => 'nullable|string|min:3|max:30|alpha_dash|unique:users,username',
            'email'         => 'required|email|max:190|unique:users,email',
            'phone'         => 'required|string|max:30',
            'password'      => 'required|string|min:6|confirmed',
            'referral_code' => 'nullable|string|max:20',
        ]);

        // Parrainage : invite un ami = crédit pour les deux
        $referrer = null;
        if (! empty($data['referral_code'])) {
            $referrer = User::where('referral_code', strtoupper(trim($data['referral_code'])))->first();

            if (! $referrer) {
                return back()->withErrors(['referral_code' => __('Code parrain invalide.')])->withInput();
            }
        }

        $user = DB::transaction(function () use ($data, $referrer) {
            $user = User::create([
                'name'          => $data['name'],
                'first_name'    => $data['first_name'],
                'username'      => $data['username'] ?? null,
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'password'      => $data['password'],
                'role'          => 'customer',
                'referral_code' => User::generateReferralCode(),
                'referred_by'   => $referrer?->id,
            ]);

            if ($referrer) {
                $bonus = (int) setting('referral_bonus', 500);

                $referrer->increment('store_credit', $bonus);
                $referrer->creditTransactions()->create([
                    'amount'      => $bonus,
                    'type'        => 'referral',
                    'description' => __('Bonus parrainage : :name', ['name' => $user->full_name]),
                ]);

                $user->increment('store_credit', $bonus);
                $user->creditTransactions()->create([
                    'amount'      => $bonus,
                    'type'        => 'referral',
                    'description' => __('Bonus de bienvenue (code :code)', ['code' => $referrer->referral_code]),
                ]);
            }

            return $user;
        });

        Auth::login($user);

        return redirect()->route('home')->with('success', __('Bienvenue sur MultiShop, :name !', ['name' => $user->first_name]));
    }

    // ------------------------------------------------------------ Inscription vendeur

    public function showRegisterSeller()
    {
        return view('auth.register-seller');
    }

    public function registerSeller(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'first_name'       => 'required|string|max:100',
            'username'         => 'nullable|string|min:3|max:30|alpha_dash|unique:users,username',
            'email'            => 'required|email|max:190|unique:users,email',
            'phone'            => 'required|string|max:30',
            'password'         => 'required|string|min:6|confirmed',
            'shop_name'        => 'required|string|max:100',
            'shop_description' => 'required|string|max:2000',
            'city'             => 'required|string|max:100',
            'address'          => 'required|string|max:200',
            'whatsapp'         => 'nullable|string|max:30',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'          => $data['name'],
                'first_name'    => $data['first_name'],
                'username'      => $data['username'] ?? null,
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'password'      => $data['password'],
                'role'          => 'seller',
                'referral_code' => User::generateReferralCode(),
            ]);

            $slug = Str::slug($data['shop_name']);
            $base = $slug;
            $i = 1;
            while (Shop::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }

            Shop::create([
                'user_id'     => $user->id,
                'name'        => $data['shop_name'],
                'slug'        => $slug,
                'description' => $data['shop_description'],
                'phone'       => $data['phone'],
                'whatsapp'    => $data['whatsapp'] ?? $data['phone'],
                'address'     => $data['address'],
                'city'        => $data['city'],
                'status'      => 'pending',
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('seller.pending');
    }
}
