<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_number',
    'customer_id',
    'status',
    'subtotal',
    'discount',
    'additional_fee',
    'tax',
    'total',
    'notes',
    'received_at',
    'promised_at',
    'completed_at',
    'created_by',
])]
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'additional_fee' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'received_at' => 'datetime',
            'promised_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shoes(): HasMany
    {
        return $this->hasMany(Shoe::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function qualityControl(): HasOne
    {
        return $this->hasOne(QualityControl::class)->latestOfMany();
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', OrderStatus::Cancelled->value);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::ShoesReceived->value,
            OrderStatus::Inspection->value,
            OrderStatus::Washing->value,
            OrderStatus::Drying->value,
            OrderStatus::Finishing->value,
            OrderStatus::QualityControl->value,
        ]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $inner) use ($term): void {
            $inner->where('order_number', 'like', "%{$term}%")
                ->orWhereHas('customer', fn (Builder $q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('customer_code', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
        });
    }

    /**
     * Orders placed on the given day, used for the dashboard totals.
     */
    public function scopeCreatedOn(Builder $query, \DateTimeInterface $date): Builder
    {
        $start = \Illuminate\Support\Carbon::instance($date)->startOfDay();

        return $query->whereBetween('created_at', [$start, $start->copy()->endOfDay()]);
    }

    /**
     * Recompute the stored totals from the persisted line items.
     *
     * The browser is never trusted with money; this is the authoritative
     * recalculation used after any change to the cart.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');

        $this->subtotal = $subtotal;
        $this->total = max(0, $subtotal - $this->discount + $this->additional_fee + $this->tax);
    }

    /**
     * Amount settled so far across every payment record.
     */
    public function paidAmount(): float
    {
        return (float) $this->payments()->whereNotIn('status', [PaymentStatus::Refunded->value])->sum('amount');
    }

    /**
     * Derive the payment status from what has actually been recorded.
     */
    public function paymentStatus(): PaymentStatus
    {
        if ($this->payments()->where('status', PaymentStatus::Refunded->value)->exists()) {
            return PaymentStatus::Refunded;
        }

        $paid = $this->paidAmount();

        if ($paid <= 0) {
            return PaymentStatus::Unpaid;
        }

        return $paid + 0.009 >= (float) $this->total ? PaymentStatus::Paid : PaymentStatus::Partial;
    }

    public function isPaid(): bool
    {
        return $this->paymentStatus() === PaymentStatus::Paid;
    }

    /**
     * Whether the order may be moved forward right now.
     */
    public function canAdvance(): bool
    {
        return $this->status->nextStatus() !== null;
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}