<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'service_category_id',
    'name',
    'slug',
    'description',
    'price',
    'estimated_duration',
    'is_active',
])]
class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            // decimal:... keeps the value exact; no float rounding creeps in.
            'price' => 'decimal:2',
            'estimated_duration' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Estimated turnaround time rendered for staff, e.g. "2 hari".
     */
    public function getDurationLabelAttribute(): string
    {
        $days = max(1, (int) $this->estimated_duration);

        return $days.' hari';
    }

    /**
     * Price formatted as Indonesian Rupiah, e.g. "Rp35.000".
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp'.number_format((float) $this->price, 0, ',', '.');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}