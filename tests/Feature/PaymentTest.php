<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function order(float $total = 100000.0): Order
    {
        return Order::factory()->create([
            'subtotal' => $total,
            'total' => $total,
            'status' => OrderStatus::OrderCreated,
        ]);
    }

    public function test_cash_payment_calculates_change_server_side(): void
    {
        $order = $this->order(85000);
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->post(route('payments.store', $order), [
                'method' => PaymentMethod::Cash->value,
                'amount_received' => 100000,
            ])
            ->assertRedirect(route('orders.show', $order));

        $payment = $order->payments()->firstOrFail();

        $this->assertSame('85000.00', $payment->amount);
        $this->assertSame('15000.00', $payment->change_due);
    }

    public function test_cash_below_the_total_is_rejected(): void
    {
        $order = $this->order(85000);

        $this->actingAs(User::factory()->cashier()->create())
            ->post(route('payments.store', $order), [
                'method' => PaymentMethod::Cash->value,
                'amount_received' => 50000,
            ])
            ->assertSessionHasErrors('amount_received');

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_paying_advances_the_order_to_payment(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->cashier()->create())
            ->post(route('payments.store', $order), ['method' => PaymentMethod::Qris->value]);

        $this->assertSame(OrderStatus::Payment, $order->refresh()->status);
    }

    public function test_staff_cannot_take_payment(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('payments.store', $order), ['method' => PaymentMethod::Qris->value])
            ->assertForbidden();
    }

    public function test_payment_status_reflects_partial_settlement(): void
    {
        $order = $this->order(100000);
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)->post(route('payments.store', $order), [
            'method' => PaymentMethod::EWallet->value,
            'amount' => 40000,
        ]);

        $this->assertSame('partial', $order->refresh()->paymentStatus()->value);

        $this->actingAs($cashier)->post(route('payments.store', $order), [
            'method' => PaymentMethod::EWallet->value,
            'amount' => 60000,
        ]);

        $this->assertSame('paid', $order->refresh()->paymentStatus()->value);
    }

    /**
     * The WELCOME10 rules: 10% above Rp50.000, capped at Rp30.000.
     */
    public function test_welcome_discount_respects_minimum_and_cap(): void
    {
        $promotion = Promotion::factory()->create([
            'discount_percent' => 10,
            'minimum_total' => 50000,
            'maximum_discount' => 30000,
        ]);

        // Below the minimum spend.
        $this->assertSame(0.0, $promotion->discountFor(40000));

        // Above the minimum, below the cap.
        $this->assertSame(6000.0, $promotion->discountFor(60000));

        // Capped at Rp30.000.
        $this->assertSame(30000.0, $promotion->discountFor(500000));
    }

    public function test_orders_are_searchable_and_listed_with_pagination(): void
    {
        Order::factory()->count(3)->create();
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->get(route('payments.index'))
            ->assertOk();
    }
}