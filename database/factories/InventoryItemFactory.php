<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'unit' => 'pcs',
            'quantity' => fake()->numberBetween(5, 100),
            'minimum_stock' => fake()->numberBetween(5, 20),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (): array => [
            'quantity' => 2,
            'minimum_stock' => 5,
        ]);
    }
}