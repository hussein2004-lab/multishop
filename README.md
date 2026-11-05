# 🛍️ MultiShop — Tout ce dont vous avez besoin

Marketplace multi-boutiques avec assistant IA, conçue pour la Côte d'Ivoire.
Les boutiques locales s'inscrivent, les clients commandent, paient en ligne
(avec vérification OTP) ou à la livraison, et se font livrer grâce à la
localisation sur carte.

**Stack** : Laravel 12 (PHP 8.2+) · MySQL · Bootstrap 5 · GSAP · Leaflet

## Installation rapide (XAMPP)

> Le guide complet pas à pas se trouve dans `Manuel_Utilisation_MultiShop.pdf`.

1. Installer [XAMPP](https://www.apachefriends.org/) (PHP 8.2+) et [Composer](https://getcomposer.org/).
2. Démarrer **Apache** et **MySQL** dans XAMPP, puis créer la base `multishop`
   dans phpMyAdmin (http://localhost/phpmyadmin).
3. Dans le dossier du projet :

   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   php artisan serve
   ```

4. Ouvrir **http://localhost:8000** 🎉

## Comptes par défaut

| Rôle          | Identifiant            | Mot de passe |
|---------------|------------------------|--------------|
| Administrateur| `ayman`                | `ayman123`   |
| Vendeur démo  | `vendeur1@multishop.ci`| `vendeur123` |
| Client démo   | `client@multishop.ci`  | `client123`  |

## Fonctionnalités

- 🤖 **Assistant IA** : « je veux un téléphone à 100 000 FCFA » → recommandations.
  Moteur local intelligent inclus ; ajoutez `ANTHROPIC_API_KEY` dans `.env`
  pour passer sur l'API Claude.
- 🏪 Inscription des boutiques avec validation par l'administrateur
- 🛡️ Score de **fiabilité des vendeurs** (livraisons + avis clients) et filtre dédié
- 🛒 Panier multi-boutiques, commande avec **position sur la carte** (Leaflet/OpenStreetMap + lien Google Maps)
- 💳 Paiement carte/PayPal **simulé** avec **vérification OTP par SMS** (code visible à l'écran en mode démo) ou 💵 espèces à la livraison
- 🎁 **Parrainage** (invite un ami = crédit pour les deux)
- 🏆 **Points de fidélité** gagnés à chaque livraison, utilisables en remise
- 🔥 **Classement des produits tendance**
- 📲 **Partage WhatsApp en 1 clic** (produits + lien de parrainage)
- 🎨 4 thèmes : clair (défaut), sombre, sépia, gris clair
- 🌍 7 langues : français (défaut), English, العربية (RTL), Español, Deutsch, 日本語, 中文
- ✨ Le site **s'adapte aux préférences** : section « Pour vous », catégories réordonnées, thème et langue mémorisés
- 👑 Espace admin complet : boutiques, utilisateurs, modération, commandes, statistiques (graphiques), paramètres du site, ajout d'administrateurs

## Paiement réel (production)

Le paiement en ligne est une **simulation réaliste** (Luhn + OTP). Pour passer
en production, branchez un vrai prestataire (CinetPay, Paystack, Stripe,
PayPal SDK...) dans `app/Http/Controllers/CheckoutController.php` et un vrai
fournisseur SMS (Orange, MTN, Twilio...) dans `app/Services/SmsService.php`.
