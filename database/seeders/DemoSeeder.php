<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Shop::count() > 0) {
            return; // Données de démo déjà installées
        }

        $catId = fn (string $slug) => Category::where('slug', $slug)->value('id');

        // ------------------------------------------------------------
        // Vendeurs + boutiques (mot de passe : vendeur123)
        // ------------------------------------------------------------
        $shopsData = [
            ['Koné', 'Moussa', 'vendeur1', "Abidjan Phone Store", "Téléphones, tablettes et accessoires 100% originaux, garantis, aux meilleurs prix d'Abidjan. Livraison rapide partout en ville.", 'Cocody', 5.3599, -3.9810],
            ['Traoré', 'Aminata', 'vendeur2', "Élégance Mode CI", "Prêt-à-porter, pagnes wax, sacs et chaussures tendance pour femmes et hommes. La mode ivoirienne à votre porte.", 'Treichville', 5.2932, -4.0126],
            ['Kouassi', 'Yao', 'vendeur3', "TechPro CI", "Ordinateurs, imprimantes, accessoires informatiques et électronique grand public. Conseil et SAV professionnels.", 'Plateau', 5.3252, -4.0214],
            ['Diabaté', 'Fatou', 'vendeur4', "Marché Plus", "Votre supermarché en ligne : alimentation, produits ménagers et articles de cuisine livrés chez vous.", 'Yopougon', 5.3456, -4.0892],
        ];

        $shops = [];
        foreach ($shopsData as $i => [$nom, $prenom, $username, $shopName, $desc, $city, $lat, $lng]) {
            $seller = User::create([
                'name'          => $nom,
                'first_name'    => $prenom,
                'username'      => $username,
                'email'         => $username.'@multishop.ci',
                'phone'         => '+225 070102030'.$i,
                'password'      => Hash::make('vendeur123'),
                'role'          => 'seller',
                'referral_code' => 'MS-VEND0'.($i + 1),
            ]);

            $shops[] = Shop::create([
                'user_id'     => $seller->id,
                'name'        => $shopName,
                'slug'        => Str::slug($shopName),
                'description' => $desc,
                'phone'       => '+225 070102030'.$i,
                'whatsapp'    => '22507010203'.$i,
                'address'     => $city.', Abidjan',
                'city'        => $city,
                'latitude'    => $lat,
                'longitude'   => $lng,
                'status'      => 'approved',
            ]);
        }

        // Boutique en attente de validation (pour la démo admin)
        $pendingSeller = User::create([
            'name'          => 'Bamba',
            'first_name'    => 'Sékou',
            'username'      => 'vendeur5',
            'email'         => 'vendeur5@multishop.ci',
            'phone'         => '+225 0701020399',
            'password'      => Hash::make('vendeur123'),
            'role'          => 'seller',
            'referral_code' => 'MS-VEND05',
        ]);
        Shop::create([
            'user_id'     => $pendingSeller->id,
            'name'        => "Bijoux d'Or Abidjan",
            'slug'        => 'bijoux-d-or-abidjan',
            'description' => "Bijoux en or et argent, montres et accessoires de luxe.",
            'phone'       => '+225 0701020399',
            'whatsapp'    => '2250701020399',
            'address'     => 'Adjamé, Abidjan',
            'city'        => 'Adjamé',
            'status'      => 'pending',
        ]);

        // ------------------------------------------------------------
        // Produits [boutique, catégorie, nom, prix, ancien prix, stock, vues, ventes, description]
        // ------------------------------------------------------------
        $tel  = 'telephones-tablettes';
        $info = 'informatique-electronique';
        $mode = 'mode-vetements';
        $sante = 'beaute-sante';
        $maison = 'maison-cuisine';
        $alim = 'alimentation';
        $sport = 'sport-loisirs';

        $products = [
            // Abidjan Phone Store
            [0, $tel,  "Tecno Spark 20", 85000, 95000, 25, 340, 42, "Écran 6,6\" 90 Hz, 8 Go RAM, 128 Go, batterie 5000 mAh, double SIM. Garantie 12 mois."],
            [0, $tel,  "Infinix Hot 40", 95000, null, 18, 290, 35, "Écran 6,78\" FHD+, 8 Go RAM, 256 Go, charge rapide 33 W. Garantie 12 mois."],
            [0, $tel,  "Samsung Galaxy A16", 120000, 135000, 15, 410, 38, "Écran Super AMOLED 6,7\", 4 Go RAM, 128 Go, triple capteur 50 MP. Garantie officielle Samsung."],
            [0, $tel,  "Redmi A3", 60000, null, 30, 220, 28, "Écran 6,71\" 90 Hz, 4 Go RAM, 128 Go, batterie 5000 mAh. Parfait premier smartphone."],
            [0, $tel,  "itel A70", 45000, 52000, 40, 180, 31, "Écran 6,6\", 4 Go RAM (extensible), 256 Go, batterie 5000 mAh. Le meilleur rapport qualité/prix."],
            [0, $tel,  "iPhone 13 128 Go", 350000, 380000, 6, 520, 12, "Apple iPhone 13, puce A15 Bionic, double caméra 12 MP, Face ID. Reconditionné premium, garantie 6 mois."],
            [0, $tel,  "Samsung Galaxy S23", 550000, null, 4, 310, 5, "Écran Dynamic AMOLED 6,1\", 8 Go RAM, 256 Go, Snapdragon 8 Gen 2. Neuf, scellé."],
            [0, $tel,  "Tablette Lenovo Tab M10", 150000, null, 10, 150, 9, "Écran 10,1\" FHD, 4 Go RAM, 64 Go, idéale pour les cours et le divertissement."],
            [0, $info, "Écouteurs Bluetooth Oraimo", 15000, 18000, 50, 260, 64, "Écouteurs sans fil avec réduction de bruit, autonomie 30 h avec le boîtier."],
            [0, $info, "Powerbank Oraimo 20000 mAh", 12000, null, 45, 200, 58, "Batterie externe charge rapide 2 ports USB. Indispensable pendant les coupures."],

            // Élégance Mode CI
            [1, $mode, "Robe en pagne wax", 25000, 30000, 20, 380, 33, "Robe cousue main en véritable pagne wax hollandais. Tailles S à XXL, coupe moderne."],
            [1, $mode, "Chemise homme en pagne", 18000, null, 25, 240, 27, "Chemise élégante en pagne, parfaite pour le bureau ou les cérémonies. Tailles M à XXL."],
            [1, $mode, "Sac à main cuir", 22000, 28000, 15, 210, 19, "Sac à main en cuir véritable, finitions soignées, plusieurs coloris disponibles."],
            [1, $mode, "Baskets sport unisexe", 35000, null, 30, 330, 41, "Baskets confortables et résistantes, semelle amortissante. Pointures 38 à 45."],
            [1, $mode, "Boubou brodé grand boubou", 40000, 48000, 12, 190, 14, "Grand boubou brodé 3 pièces, tissu bazin riche. Confection sur mesure possible."],
            [1, $sante, "Coffret soins karité", 12500, null, 35, 170, 26, "Beurre de karité pur, savon noir et huile de coco : le trio beauté 100% naturel."],

            // TechPro CI
            [2, $info, "Ordinateur portable HP 15", 320000, 350000, 8, 450, 11, "HP 15, Intel Core i5 12e gén., 8 Go RAM, SSD 512 Go, Windows 11. Garantie 12 mois."],
            [2, $info, "Souris sans fil Logitech", 8000, null, 60, 140, 47, "Souris sans fil silencieuse, capteur précis, autonomie 12 mois."],
            [2, $info, "Clavier AZERTY rétroéclairé", 12000, 15000, 40, 120, 22, "Clavier AZERTY français rétroéclairé, touches silencieuses, USB."],
            [2, $info, "Écran 24\" FHD", 95000, null, 12, 230, 13, "Moniteur 24 pouces Full HD, dalle IPS, HDMI + VGA. Idéal bureau et gaming léger."],
            [2, $info, "Imprimante Canon multifonction", 85000, 99000, 10, 200, 8, "Impression, copie et scan. Wi-Fi, cartouches économiques. Garantie 12 mois."],
            [2, $tel,  "Montre connectée Oraimo", 25000, 32000, 28, 310, 36, "Écran AMOLED, suivi santé et sport, appels Bluetooth, autonomie 7 jours."],

            // Marché Plus
            [3, $alim, "Riz parfumé 25 kg", 18500, null, 100, 290, 88, "Sac de riz parfumé long grain 25 kg, qualité supérieure."],
            [3, $alim, "Huile végétale 5 L", 9500, 11000, 80, 220, 73, "Bidon d'huile végétale raffinée 5 litres."],
            [3, $alim, "Attiéké frais 1 kg", 1500, null, 150, 180, 95, "Attiéké frais de qualité, conditionné sous vide. Livraison le jour même."],
            [3, $maison, "Mixeur blender 1,5 L", 28000, 34000, 20, 250, 21, "Blender 600 W, bol en verre 1,5 L, 3 vitesses. Garantie 6 mois."],
            [3, $maison, "Ventilateur sur pied", 35000, null, 15, 270, 17, "Ventilateur sur pied 3 vitesses, oscillant, silencieux. Indispensable en saison chaude."],
            [3, $maison, "Set 3 marmites inox", 45000, 52000, 18, 160, 12, "Set de 3 marmites en inox avec couvercles, tous feux. Qualité professionnelle."],
            [3, $sport, "Ballon de football taille 5", 12000, null, 30, 130, 24, "Ballon de football cousu machine, taille officielle 5."],
        ];

        $productModels = [];
        foreach ($products as [$shopIdx, $catSlug, $name, $price, $oldPrice, $stock, $views, $sales, $desc]) {
            $productModels[] = Product::create([
                'shop_id'     => $shops[$shopIdx]->id,
                'category_id' => $catId($catSlug),
                'name'        => $name,
                'slug'        => Str::slug($name),
                'description' => $desc,
                'price'       => $price,
                'old_price'   => $oldPrice,
                'stock'       => $stock,
                'views_count' => $views,
                'sales_count' => $sales,
            ]);
        }

        // ------------------------------------------------------------
        // Clients de démo (mot de passe : client123)
        // ------------------------------------------------------------
        $client = User::create([
            'name'          => 'Bamba',
            'first_name'    => 'Awa',
            'username'      => 'awa',
            'email'         => 'client@multishop.ci',
            'phone'         => '+225 0709080706',
            'password'      => Hash::make('client123'),
            'role'          => 'customer',
            'referral_code' => 'MS-AWA001',
        ]);

        // Filleul parrainé par Awa (démo du parrainage)
        $client2 = User::create([
            'name'          => 'Ouattara',
            'first_name'    => 'Ibrahim',
            'username'      => 'ibrahim',
            'email'         => 'client2@multishop.ci',
            'phone'         => '+225 0709080705',
            'password'      => Hash::make('client123'),
            'role'          => 'customer',
            'referral_code' => 'MS-IBRA01',
            'referred_by'   => $client->id,
        ]);

        $bonus = 500;
        foreach ([[$client, 'Bonus parrainage : Ibrahim O.'], [$client2, 'Bonus de bienvenue (code MS-AWA001)']] as [$user, $label]) {
            $user->increment('store_credit', $bonus);
            $user->creditTransactions()->create([
                'amount'      => $bonus,
                'type'        => 'referral',
                'description' => $label,
            ]);
        }

        // ------------------------------------------------------------
        // Commandes de démonstration (3 derniers mois)
        // ------------------------------------------------------------
        $statuses = ['delivered', 'delivered', 'delivered', 'delivered', 'delivered', 'delivered',
                     'delivered', 'delivered', 'delivered', 'delivered', 'delivered',
                     'shipped', 'shipped', 'confirmed', 'confirmed', 'pending', 'pending', 'cancelled'];
        $methods  = ['cash', 'card', 'paypal'];
        $customers = [$client, $client2];

        foreach ($statuses as $i => $status) {
            $shop = $shops[$i % 4];
            $customer = $customers[$i % 2];
            $shopProducts = collect($productModels)->where('shop_id', $shop->id)->values();
            $picked = $shopProducts->random(min(2, $shopProducts->count()));

            $createdAt = now()->subDays(rand(2, 95))->setTime(rand(8, 20), rand(0, 59));
            $method = $methods[$i % 3];

            $subtotal = 0;
            $itemsData = [];
            foreach ($picked as $p) {
                $qty = rand(1, 2);
                $subtotal += $p->price * $qty;
                $itemsData[] = [$p, $qty];
            }

            $deliveryFee = 1000;
            $total = $subtotal + $deliveryFee;
            $isDelivered = $status === 'delivered';
            $isOnline = $method !== 'cash';

            $order = Order::create([
                'group_code'       => strtoupper(Str::random(10)),
                'user_id'          => $customer->id,
                'shop_id'          => $shop->id,
                'status'           => $status,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'discount'         => 0,
                'total'            => $total,
                'payment_method'   => $method,
                'payment_status'   => ($isDelivered || ($isOnline && $status !== 'cancelled')) ? 'paid' : 'pending',
                'delivery_name'    => $customer->first_name.' '.$customer->name,
                'delivery_phone'   => $customer->phone,
                'delivery_address' => 'Riviera Palmeraie, Abidjan',
                'latitude'         => 5.36 + rand(-300, 300) / 10000,
                'longitude'        => -3.98 + rand(-300, 300) / 10000,
                'points_earned'    => $isDelivered ? intdiv($total, 1000) : 0,
                'paid_at'          => ($isOnline && $status !== 'cancelled') ? $createdAt : ($isDelivered ? $createdAt->copy()->addDays(2) : null),
                'delivered_at'     => $isDelivered ? $createdAt->copy()->addDays(2) : null,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            foreach ($itemsData as [$p, $qty]) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $p->id,
                    'product_name' => $p->name,
                    'unit_price'   => $p->price,
                    'quantity'     => $qty,
                    'line_total'   => $p->price * $qty,
                ]);
            }

            if ($isDelivered) {
                $points = intdiv($total, 1000);
                $customer->increment('loyalty_points', $points);
                $customer->loyaltyTransactions()->create([
                    'order_id'    => $order->id,
                    'points'      => $points,
                    'type'        => 'earn',
                    'description' => 'Commande #'.$order->id.' livrée',
                ]);
            }
        }

        // ------------------------------------------------------------
        // Avis clients sur les commandes livrées → fiabilité des boutiques
        // ------------------------------------------------------------
        $comments = [
            [5, "Livraison rapide et produit conforme, je recommande vivement !"],
            [4, "Très bon vendeur, article de qualité. Petit retard de livraison."],
            [5, "Excellent service, le vendeur est très professionnel."],
            [5, "Produit authentique, bien emballé. Merci !"],
            [4, "Bon rapport qualité/prix, service client réactif."],
            [3, "Produit correct mais la livraison a pris du temps."],
            [5, "Parfait du début à la fin. Je rachèterai ici."],
            [4, "Conforme à la description, vendeur sérieux."],
        ];

        $deliveredOrders = Order::where('status', 'delivered')->take(count($comments))->get();
        foreach ($deliveredOrders as $i => $order) {
            [$rating, $comment] = $comments[$i];
            Review::create([
                'order_id' => $order->id,
                'user_id'  => $order->user_id,
                'shop_id'  => $order->shop_id,
                'rating'   => $rating,
                'comment'  => $comment,
            ]);
        }

        // Mise à jour des statistiques de fiabilité de chaque boutique
        foreach (Shop::all() as $shop) {
            $shop->refreshReliabilityStats();
        }
    }
}
