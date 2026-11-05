<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/boutiques', [ShopController::class, 'index'])->name('shops.index');
Route::get('/boutique/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');

Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// 🤖 Assistant IA : "je veux un téléphone à 100 000 FCFA"
Route::get('/assistant', [AiController::class, 'index'])->name('assistant');

// 🛒 Panier
Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier/ajouter/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// 🎨 Préférences (thème + langue), accessibles aussi sans compte
Route::post('/preferences/theme', [PreferenceController::class, 'theme'])->name('preferences.theme');
Route::post('/preferences/langue', [PreferenceController::class, 'locale'])->name('preferences.locale');

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.post');
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->name('register.post');
    Route::get('/devenir-vendeur', [AuthController::class, 'showRegisterSeller'])->name('register.seller');
    Route::post('/devenir-vendeur', [AuthController::class, 'registerSeller'])->name('register.seller.post');
});

Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Client connecté
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Commande
    Route::get('/commander', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/commander', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/commande/succes/{group}', [CheckoutController::class, 'success'])->name('checkout.success');

    // Paiement en ligne + vérification OTP
    Route::get('/paiement/{group}', [CheckoutController::class, 'payment'])->name('payment.show');
    Route::post('/paiement/{group}/carte', [CheckoutController::class, 'processCard'])->name('payment.card');
    Route::post('/paiement/{group}/paypal', [CheckoutController::class, 'processPaypal'])->name('payment.paypal');
    Route::get('/paiement/{group}/verification', [CheckoutController::class, 'otp'])->name('payment.otp');
    Route::post('/paiement/{group}/verification', [CheckoutController::class, 'verifyOtp'])->name('payment.otp.verify');
    Route::post('/paiement/{group}/renvoyer', [CheckoutController::class, 'resendOtp'])->name('payment.otp.resend');

    // Mes commandes
    Route::get('/mes-commandes', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/commande/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/commande/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/commande/{order}/avis', [OrderController::class, 'review'])->name('orders.review');

    // Mon compte (profil, fidélité, parrainage)
    Route::get('/mon-compte', [AccountController::class, 'index'])->name('account.index');
    Route::put('/mon-compte', [AccountController::class, 'update'])->name('account.update');
});

/*
|--------------------------------------------------------------------------
| Espace vendeur
|--------------------------------------------------------------------------
*/

Route::get('/vendeur/en-attente', [\App\Http\Controllers\Seller\DashboardController::class, 'pending'])
    ->middleware('auth')->name('seller.pending');

Route::prefix('vendeur')->name('seller.')->middleware(['auth', 'seller'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Seller\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produits', [\App\Http\Controllers\Seller\ProductController::class, 'index'])->name('products.index');
    Route::get('/produits/nouveau', [\App\Http\Controllers\Seller\ProductController::class, 'create'])->name('products.create');
    Route::post('/produits', [\App\Http\Controllers\Seller\ProductController::class, 'store'])->name('products.store');
    Route::get('/produits/{product}/modifier', [\App\Http\Controllers\Seller\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/produits/{product}', [\App\Http\Controllers\Seller\ProductController::class, 'update'])->name('products.update');
    Route::delete('/produits/{product}', [\App\Http\Controllers\Seller\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/commandes', [\App\Http\Controllers\Seller\OrderController::class, 'index'])->name('orders.index');
    Route::post('/commandes/{order}/statut', [\App\Http\Controllers\Seller\OrderController::class, 'updateStatus'])->name('orders.status');
});

/*
|--------------------------------------------------------------------------
| Espace administrateur
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Valide ou refuse l'inscription des magasins
    Route::get('/boutiques', [\App\Http\Controllers\Admin\ShopController::class, 'index'])->name('shops.index');
    Route::post('/boutiques/{shop}/approuver', [\App\Http\Controllers\Admin\ShopController::class, 'approve'])->name('shops.approve');
    Route::post('/boutiques/{shop}/refuser', [\App\Http\Controllers\Admin\ShopController::class, 'reject'])->name('shops.reject');

    // Gère les utilisateurs
    Route::get('/utilisateurs', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/utilisateurs/{user}/basculer', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle');

    // Modère les produits
    Route::get('/produits', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/produits/{product}/visibilite', [\App\Http\Controllers\Admin\ProductController::class, 'toggleVisible'])->name('products.toggle');
    Route::delete('/produits/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Consulte toutes les commandes
    Route::get('/commandes', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');

    // Configure les paramètres du site
    Route::get('/parametres', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/parametres', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Ajoute d'autres administrateurs
    Route::get('/administrateurs', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admins.index');
    Route::post('/administrateurs', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('admins.store');
});
