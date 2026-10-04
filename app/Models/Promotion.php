<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'name',
    'description',
    'discount_percent',
    'minimum_total',
    'maximum_discount',
    'is_active',
    'starts_at',
    'ends_at',
    'usage_limit',
])]
class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'minimum_total' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether the promotion may be redeemed at all right now.
     */
    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at !== null && $now->gt($this->ends_at)) {
            return false;
        }

        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    /**
     * Whether a given subtotal clears the minimum spend.
     */
    public function meetsMinimum(float $subtotal): bool
    {
        return $subtotal >= (float) $this->minimum_total;
    }

    /**
     * The discount this promotion yields for a subtotal, capped server-side.
     *
     * Integer cents are used throughout so no floating point rounding can
     * hand out a fraction of a rupiah.
     */
    public function discountFor(float $subtotal): float
    {
        if (! $this->meetsMinimum($subtotal) || $this->discount_percent <= 0) {
            return 0.0;
        }

        $percentCents = (int) round($subtotal * 100) * $this->discount_percent;
        $discount = intdiv($percentCents, 100 * 100);

        $cap = $this->maximum_discount !== null
            ? (int) round((float) $this->maximum_discount * 100)
            : PHP_INT_MAX;

        return min($discount, $cap) / 100;
    }
}