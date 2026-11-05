<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'logo', 'phone', 'whatsapp',
        'address', 'city', 'latitude', 'longitude', 'status', 'rejection_reason',
        'rating_avg', 'rating_count', 'delivered_count', 'cancelled_count',
    ];

    protected function casts(): array
    {
        return [
            'rating_avg' => 'float',
            'latitude'   => 'float',
            'longitude'  => 'float',
        ];
    }

    // ------------------------------------------------------------ Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    // ------------------------------------------------------------ Scopes

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    // ------------------------------------------------------------ Fiabilité

    /**
     * Score de fiabilité du vendeur (0 à 100), null si trop peu de données.
     * 60% : taux de commandes livrées avec succès. 40% : note moyenne clients.
     */
    public function getReliabilityAttribute(): ?int
    {
        $completed = $this->delivered_count + $this->cancelled_count;

        if ($completed === 0 && $this->rating_count === 0) {
            return null; // Nouveau vendeur
        }

        $deliveryScore = $completed > 0 ? ($this->delivered_count / $completed) : 1;
        $ratingScore   = $this->rating_count > 0 ? ($this->rating_avg / 5) : $deliveryScore;

        return (int) round(($deliveryScore * 60 + $ratingScore * 40));
    }

    /** Recalcule les statistiques de fiabilité depuis les avis et commandes. */
    public function refreshReliabilityStats(): void
    {
        $this->rating_avg      = round((float) $this->reviews()->avg('rating'), 2);
        $this->rating_count    = $this->reviews()->count();
        $this->delivered_count = $this->orders()->where('status', 'delivered')->count();
        $this->cancelled_count = $this->orders()->where('status', 'cancelled')->count();
        $this->save();
    }

    // ------------------------------------------------------------ Helpers

    public function getLogoUrlAttribute(): string
    {
        return $this->logo
            ? asset('storage/'.$this->logo)
            : asset('images/shop-placeholder.svg');
    }

    public function getWhatsappLinkAttribute(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/[^0-9]/', '', $this->whatsapp);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
