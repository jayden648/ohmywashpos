<?php

namespace Database\Factories;

use App\Enums\QualityCheck;
use App\Models\Order;
use App\Models\QualityControl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityControl>
 */
class QualityControlFactory extends Factory
{
    protected $model = QualityControl::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'checked_by' => User::factory()->staff(),
            'checks' => static::passedChecks(),
            'notes' => null,
            'checked_at' => now(),
        ];
    }

    /**
     * A checklist with every item ticked.
     *
     * @return array<string, bool>
     */
    public static function passedChecks(): array
    {
        return collect(QualityCheck::cases())
            ->mapWithKeys(fn (QualityCheck $check): array => [$check->value => true])
            ->all();
    }

    /**
     * A checklist with one item deliberately left unticked.
     */
    public function failed(QualityCheck $check): static
    {
        return $this->state(fn (): array => [
            'checks' => array_merge(static::passedChecks(), [$check->value => false]),
        ]);
    }
}