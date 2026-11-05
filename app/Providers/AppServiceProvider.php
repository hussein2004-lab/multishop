<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Compatibilité avec les anciennes versions de MySQL/MariaDB (XAMPP)
        Schema::defaultStringLength(191);

        // Pagination stylée Bootstrap 5
        Paginator::useBootstrapFive();

        // Catégories disponibles dans la barre de navigation
        View::composer('layouts.app', function ($view) {
            $categories = collect();

            try {
                $categories = Category::orderBy('name')->get();
            } catch (\Throwable) {
                // Base de données pas encore migrée
            }

            $view->with('navCategories', $categories);
        });
    }
}
