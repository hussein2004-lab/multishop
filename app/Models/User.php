<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'first_name', 'username', 'email', 'phone', 'password',
        'role', 'is_active', 'theme', 'locale',
        'loyalty_points', 'store_credit', 'referral_code', 'referred_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ------------------------------------------------------------ Relations

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class)->latest();
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class)->latest();
    }

    /** Filleuls parrainés par cet utilisateur */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function productViews(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    // ------------------------------------------------------------ Helpers

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ? $this->first_name.' ' : '').$this->name);
    }

    /** Génère un code parrain unique du type MS-A1B2C3 */
    public static function generateReferralCode(): string
    {
        do {
            $code = 'MS-'.strtoupper(Str::random(6));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }
}
