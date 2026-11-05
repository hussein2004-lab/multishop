<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------
        // Paramètres du site (modifiables dans Admin > Paramètres)
        // ------------------------------------------------------------
        $settings = [
            'site_name'           => 'MultiShop',
            'site_tagline'        => 'Tout ce dont vous avez besoin',
            'contact_email'       => 'contact@multishop.ci',
            'contact_phone'       => '+225 07 00 00 00 00',
            'delivery_fee'        => '1000',   // FCFA par boutique
            'loyalty_earn_rate'   => '1',      // points gagnés par tranche de 1 000 FCFA
            'loyalty_point_value' => '10',     // valeur d'un point en FCFA
            'referral_bonus'      => '500',    // crédit FCFA offert (parrain + filleul)
            'ai_max_results'      => '6',      // nombre de produits proposés par l'IA
            'maintenance'         => '0',      // 1 = site en maintenance (admins seulement)
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // ------------------------------------------------------------
        // Administrateur par défaut : ayman / ayman123
        // ------------------------------------------------------------
        User::updateOrCreate(
            ['username' => 'ayman'],
            [
                'name'          => 'Ayman',
                'first_name'    => 'Admin',
                'email'         => 'admin@multishop.ci',
                'phone'         => '+225 07 00 00 00 01',
                'password'      => Hash::make('ayman123'),
                'role'          => 'admin',
                'referral_code' => 'MS-AYMAN1',
            ]
        );

        // ------------------------------------------------------------
        // Catégories
        // ------------------------------------------------------------
        $categories = [
            ['Téléphones & Tablettes',      'bi-phone'],
            ['Informatique & Électronique', 'bi-laptop'],
            ['Mode & Vêtements',            'bi-bag-heart'],
            ['Beauté & Santé',              'bi-heart-pulse'],
            ['Maison & Cuisine',            'bi-house-heart'],
            ['Alimentation',                'bi-basket'],
            ['Sport & Loisirs',             'bi-bicycle'],
            ['Bébés & Enfants',             'bi-balloon'],
        ];

        foreach ($categories as [$name, $icon]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon]
            );
        }

        // ------------------------------------------------------------
        // Données de démonstration (boutiques, produits, commandes)
        // ------------------------------------------------------------
        $this->call(DemoSeeder::class);
    }
}
