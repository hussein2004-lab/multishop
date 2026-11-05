<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    protected $fillable = [
        'group_code', 'user_id', 'shop_id', 'status',
        'subtotal', 'delivery_fee', 'discount', 'total',
        'payment_method', 'payment_status',
        'delivery_name', 'delivery_phone', 'delivery_address',
        'latitude', 'longitude', 'note', 'points_earned',
        'paid_at', 'delivered_at', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'      => 'datetime',
            'delivered_at' => 'datetime',
            'latitude'     => 'float',
            'longitude'    => 'float',
        ];
    }

    // ------------------------------------------------------------ Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // ------------------------------------------------------------ Helpers

    /** Couleur du badge Bootstrap selon le statut. */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'confirmed' => 'info',
            'shipped'   => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    public function getMapsLinkAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://maps.google.com/?q='.$this->latitude.','.$this->longitude;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isAwaitingOnlinePayment(): bool
    {
        return $this->payment_status === 'awaiting' && $this->payment_method !== 'cash';
    }

    // ------------------------------------------------------------ Transitions

    /** Marque la commande livrée : paiement encaissé + points de fidélité. */
    public function markDelivered(): void
    {
        $this->update([
            'status'         => 'delivered',
            'delivered_at'   => now(),
            'payment_status' => 'paid',
            'paid_at'        => $this->paid_at ?? now(),
        ]);

        // 🏆 Points de fidélité : X points par tranche de 1 000 FCFA
        $earnRate = max(0, (int) setting('loyalty_earn_rate', 1));
        $points   = intdiv($this->total, 1000) * $earnRate;

        if ($points > 0 && $this->points_earned === 0) {
            $this->update(['points_earned' => $points]);
            $this->user->increment('loyalty_points', $points);
            $this->user->loyaltyTransactions()->create([
                'order_id'    => $this->id,
                'points'      => $points,
                'type'        => 'earn',
                'description' => __('Commande #:id livrée', ['id' => $this->id]),
            ]);
        }

        $this->shop->refreshReliabilityStats();
    }

    /** Annule la commande : stock restauré + remise remboursée en crédit. */
    public function markCancelled(): void
    {
        foreach ($this->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
                $item->product->decrement('sales_count', min($item->quantity, $item->product->sales_count));
            }
        }

        if ($this->discount > 0) {
            $this->user->increment('store_credit', $this->discount);
            $this->user->creditTransactions()->create([
                'order_id'    => $this->id,
                'amount'      => $this->discount,
                'type'        => 'refund',
                'description' => __('Remboursement remise commande #:id', ['id' => $this->id]),
            ]);
        }

        $this->update(['status' => 'cancelled']);
        $this->shop->refreshReliabilityStats();
    }
}
