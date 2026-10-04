<?php

namespace App\Models;

use App\Enums\QualityCheck;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'checked_by', 'checks', 'notes', 'checked_at'])]
class QualityControl extends Model
{
    /** @use HasFactory<\Database\Factories\QualityControlFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checks' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * The checklist items that have not passed yet.
     *
     * @return array<int, QualityCheck>
     */
    public function failedChecks(): array
    {
        return QualityCheck::failed($this->checks ?? []);
    }

    /**
     * QC only counts once every required item has passed.
     */
    public function isPassed(): bool
    {
        return $this->failedChecks() === [];
    }
}