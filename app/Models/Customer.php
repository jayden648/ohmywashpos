<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_code',
    'name',
    'phone',
    'email',
    'address',
    'notes',
    'is_active',
])]
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Search by name, phone/WhatsApp or customer code.
     *
     * Digits are also matched so "0812 3456" finds "08123456".
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);
        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return $query->where(function (Builder $inner) use ($term, $digits): void {
            $inner->where('name', 'like', "%{$term}%")
                ->orWhere('customer_code', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");

            if ($digits !== '' && $digits !== $term) {
                $inner->orWhere('phone', 'like', "%{$digits}%");
            }
        });
    }

    /**
     * Orders counted with the aggregates shown in the customer table.
     */
    public function scopeWithOrderSummary(Builder $query): Builder
    {
        return $query->withCount([
            'orders',
            'orders as paid_orders_count' => fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled->value),
        ])->withSum([
            'orders as total_spending' => fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled->value),
        ], 'total');
    }

    /**
     * Orders that should count towards the customer's history.
     *
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    public function scopeCountableOrders(Builder $query): Builder
    {
        return $query->where('status', '!=', OrderStatus::Cancelled->value);
    }

    /**
     * Normalised WhatsApp link for the "Send WhatsApp" action.
     */
    public function getWhatsappLinkAttribute(): string
    {
        $number = preg_replace('/\D+/', '', (string) $this->phone) ?? '';

        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return $number;
    }
}