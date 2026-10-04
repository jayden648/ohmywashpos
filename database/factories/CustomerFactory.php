<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $sequence = 0;

        return [
            'customer_code' => 'C'.str_pad((string) (++$sequence), 3, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}