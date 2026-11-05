<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Paramètres du site. */
    public function edit()
    {
        $settings = [
            'site_name'           => setting('site_name', 'MultiShop'),
            'site_tagline'        => setting('site_tagline', ''),
            'contact_email'       => setting('contact_email', ''),
            'contact_phone'       => setting('contact_phone', ''),
            'delivery_fee'        => setting('delivery_fee', 1000),
            'loyalty_earn_rate'   => setting('loyalty_earn_rate', 1),
            'loyalty_point_value' => setting('loyalty_point_value', 10),
            'referral_bonus'      => setting('referral_bonus', 500),
            'ai_max_results'      => setting('ai_max_results', 6),
            'maintenance'         => setting('maintenance', '0'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name'           => 'required|string|max:100',
            'site_tagline'        => 'nullable|string|max:200',
            'contact_email'       => 'nullable|email|max:190',
            'contact_phone'       => 'nullable|string|max:30',
            'delivery_fee'        => 'required|integer|min:0|max:100000',
            'loyalty_earn_rate'   => 'required|integer|min:0|max:100',
            'loyalty_point_value' => 'required|integer|min:1|max:10000',
            'referral_bonus'      => 'required|integer|min:0|max:100000',
            'ai_max_results'      => 'required|integer|min:3|max:24',
        ]);

        $data['maintenance'] = $request->boolean('maintenance') ? '1' : '0';

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', __('Paramètres enregistrés.'));
    }
}
