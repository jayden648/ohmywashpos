<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => 35000,
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Paid,
            'amount_received' => 50000,
            'change_due' => 15000,
            'paid_at' => now(),
            'received_by' => User::factory()->cashier(),
        ];
    }
}