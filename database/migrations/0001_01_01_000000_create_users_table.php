<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                  // Nom
            $table->string('first_name')->nullable();                // Prénom
            $table->string('username')->nullable()->unique();        // Nom d'utilisateur
            $table->string('email')->unique();
            $table->string('phone')->nullable();                     // Contact
            $table->string('password');
            $table->enum('role', ['customer', 'seller', 'admin'])->default('customer');
            $table->boolean('is_active')->default(true);
            $table->string('theme', 10)->default('light');           // light / dark / sepia / gray
            $table->string('locale', 5)->default('fr');              // fr / en / ar / es / de / ja / zh
            $table->unsignedInteger('loyalty_points')->default(0);   // Points de fidélité
            $table->unsignedInteger('store_credit')->default(0);     // Crédit (FCFA) - parrainage
            $table->string('referral_code', 12)->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
