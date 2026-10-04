<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'brand',
    'model',
    'color',
    'size',
    'material',
    'condition_before',
    'damage_notes',
    'customer_notes',
])]
class Shoe extends Model
{
    /** @use HasFactory<\Database\Factories\ShoeFactory> */
    use HasFactory;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Short human readable description, e.g. "Nike Air Force 1 - White".
     */
    public function getLabelAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->brand,
            $this->model,
            $this->color,
        ])));
    }
}