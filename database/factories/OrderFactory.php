<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([35000, 70000, 85000, 120000]);

        return [
            'order_number' => 'OMW-'.fake()->unique()->numerify('2026####-####'),
            'customer_id' => Customer::factory(),
            'status' => OrderStatus::OrderCreated,
            'subtotal' => $subtotal,
            'discount' => 0,
            'additional_fee' => 0,
            'tax' => 0,
            'total' => $subtotal,
            'received_at' => now(),
            'created_by' => User::factory()->cashier(),
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => OrderStatus::Cancelled]);
    }
}