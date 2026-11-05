<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'shop_id', 'category_id', 'name', 'slug', 'description',
        'price', 'old_price', 'stock', 'image',
        'is_active', 'is_visible', 'views_count', 'sales_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    // ------------------------------------------------------------ Relations

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    // ------------------------------------------------------------ Scopes

    /** Produits visibles par les clients (actif, non modéré, boutique approuvée). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_visible', true)
            ->whereHas('shop', fn (Builder $q) => $q->where('status', 'approved'));
    }

    /** Classement tendance : ventes récentes et popularité. */
    public function scopeTrending(Builder $query): Builder
    {
        return $query->orderByRaw('(sales_count * 3 + views_count) DESC');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    // ------------------------------------------------------------ Helpers

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        $slug = $this->category?->slug ?? 'default';

        return asset('images/categories/'.$slug.'.svg');
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    /** Enregistre une consultation (tendances + préférences du client). */
    public function recordView(?User $user, string $sessionId): void
    {
        $this->increment('views_count');

        ProductView::create([
            'product_id'  => $this->id,
            'category_id' => $this->category_id,
            'user_id'     => $user?->id,
            'session_id'  => $sessionId,
        ]);
    }
}
