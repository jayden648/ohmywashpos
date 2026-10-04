<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\QualityCheck;
use App\Models\Customer;
use App\Models\Order;
use App\Models\QualityControl;
use App\Models\Service;
use App\Models\User;
use Database\Factories\QualityControlFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(OrderStatus $status = OrderStatus::OrderCreated): Order
    {
        return Order::factory()->create(['status' => $status]);
    }

    public function test_the_workflow_advances_one_step_at_a_time(): void
    {
        $order = $this->makeOrder();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->post(route('orders.advance', $order))
            ->assertRedirect();

        $order->refresh();

        // Order Created -> Payment
        $this->assertSame(OrderStatus::Payment, $order->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::Payment->value,
        ]);
    }

    public function test_a_completed_order_cannot_advance_further(): void
    {
        $order = $this->makeOrder(OrderStatus::PickedUp);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.advance', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::PickedUp, $order->refresh()->status);
    }

    /**
     * The QC gate: release for pickup is refused until every check passes.
     */
    public function test_ready_for_pickup_is_blocked_until_quality_control_passes(): void
    {
        $order = $this->makeOrder(OrderStatus::QualityControl);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.advance', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::QualityControl, $order->refresh()->status);
    }

    public function test_ready_for_pickup_is_allowed_once_quality_control_passes(): void
    {
        $order = $this->makeOrder(OrderStatus::QualityControl);

        QualityControl::factory()->create([
            'order_id' => $order->id,
            'checks' => QualityControlFactory::passedChecks(),
        ]);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.advance', $order))
            ->assertSessionHas('status');

        $this->assertSame(OrderStatus::ReadyForPickup, $order->refresh()->status);
    }

    public function test_a_failed_quality_control_check_blocks_release(): void
    {
        $order = $this->makeOrder(OrderStatus::QualityControl);

        $qc = QualityControl::factory()->failed(QualityCheck::CompletelyDry)->create([
            'order_id' => $order->id,
        ]);

        $this->assertFalse($qc->isPassed());

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.advance', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::QualityControl, $order->refresh()->status);
    }

    /**
     * A checklist missing keys must not count as a pass.
     */
    public function test_a_missing_quality_control_entry_counts_as_a_failure(): void
    {
        $qc = new QualityControl(['checks' => []]);

        $this->assertSame(8, count(QualityCheck::failed([])));
    }

    public function test_pickup_stamps_the_completion_time(): void
    {
        $order = $this->makeOrder(OrderStatus::ReadyForPickup);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.advance', $order));

        $order->refresh();

        $this->assertSame(OrderStatus::PickedUp, $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_a_cashier_can_cancel_but_staff_cannot(): void
    {
        $order = $this->makeOrder(OrderStatus::Washing);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('orders.cancel', $order))
            ->assertForbidden();

        $this->actingAs(User::factory()->cashier()->create())
            ->post(route('orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
    }

    public function test_a_collected_order_cannot_be_cancelled(): void
    {
        $order = $this->makeOrder(OrderStatus::PickedUp);

        $this->actingAs(User::factory()->cashier()->create())
            ->post(route('orders.cancel', $order))
            ->assertForbidden();
    }

    public function test_orders_are_searchable_by_number_and_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Gabriel distinctive']);
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        Order::factory()->create();

        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->get(route('orders.index', ['search' => $order->order_number]))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->actingAs($cashier)
            ->get(route('orders.index', ['search' => 'distinctive']))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_guests_cannot_view_orders(): void
    {
        $this->get(route('orders.index'))->assertRedirect('/login');
    }
}