<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    public const MAX_ATTEMPTS = 3;

    protected $fillable = ['user_id', 'group_code', 'code', 'attempts', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Génère un nouveau code OTP à 6 chiffres pour un paiement (valable 5 min). */
    public static function generateFor(User $user, string $groupCode): self
    {
        // Invalide les anciens codes de ce paiement
        static::where('user_id', $user->id)
            ->where('group_code', $groupCode)
            ->whereNull('used_at')
            ->delete();

        return static::create([
            'user_id'    => $user->id,
            'group_code' => $groupCode,
            'code'       => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasTooManyAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }
}
