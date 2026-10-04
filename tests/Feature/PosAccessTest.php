<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
    }

    public function test_guests_are_redirected_to_login_from_the_till(): void
    {
        $this->get('/pos')->assertRedirect('/login');
    }

    public function test_a_cashier_can_open_the_till(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Keranjang', escape: false)
            ->assertSee('Bayar Sekarang', escape: false);
    }

    public function test_staff_cannot_open_the_till(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get(route('pos.index'))
            ->assertForbidden();
    }

    public function test_the_till_lists_the_seeded_services_from_the_database(): void
    {
        $response = $this->actingAs(User::factory()->cashier()->create())
            ->get(route('pos.index'));

        $response->assertOk();

        foreach (Service::all() as $service) {
            $response->assertSee($service->name);
        }
    }

    /**
     * The prototype's reference price must reach the screen as seeded.
     */
    public function test_seeded_prices_match_the_prototype_reference(): void
    {
        $this->assertSame('35000.00', Service::where('name', 'Sepatu Olahraga')->value('price'));
        $this->assertSame('85000.00', Service::where('name', 'Premium Treatment / Cuci 3')->value('price'));
        $this->assertSame('50000.00', Service::where('name', 'Repaint (mulai)')->value('price'));
        $this->assertSame('25000.00', Service::where('name', 'Fast Service / Express')->value('price'));
    }

    public function test_a_cart_line_can_be_added_and_removed(): void
    {
        $cashier = User::factory()->cashier()->create();
        $service = Service::first();

        $this->actingAs($cashier)
            ->post(route('pos.cart'), ['action' => 'add', 'service_id' => $service->id])
            ->assertRedirect();

        $this->assertSame([['service_id' => $service->id, 'quantity' => 1]], session('pos.cart'));

        $this->actingAs($cashier)
            ->post(route('pos.cart'), ['action' => 'remove', 'service_id' => $service->id])
            ->assertRedirect();

        $this->assertSame([], session('pos.cart'));
    }

    public function test_an_order_is_created_with_totals_recalculated_from_the_database(): void
    {
        $cashier = User::factory()->cashier()->create();
        $customer = Customer::factory()->create();

        $service = Service::where('name', 'Sepatu Olahraga')->firstOrFail();

        // A hostile client tries to dictate its own price and total.
        $this->actingAs($cashier)
            ->withSession(['pos.cart' => [['service_id' => $service->id, 'quantity' => 2]]])
            ->post(route('pos.store'), [
                'customer_id' => $customer->id,
                'items' => [
                    ['service_id' => $service->id, 'quantity' => 2],
                ],
                'subtotal' => 1,
                'total' => 1,
                'unit_price' => 1,
                'shoe' => ['brand' => 'Nike', 'size' => '42'],
            ])
            ->assertRedirect();

        $order = $customer->orders()->latest('id')->firstOrFail();

        $this->assertSame('order_created', $order->status->value);
        // 35000 x 2, priced by the server from the services table.
        $this->assertSame('70000.00', $order->subtotal);
        $this->assertSame('70000.00', $order->total);
        $this->assertSame('OMW-', substr($order->order_number, 0, 4));
        $this->assertSame(1, $order->items()->count());
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::OrderCreated->value,
        ]);
    }

    public function test_an_order_requires_a_customer_and_shoe_details(): void
    {
        $cashier = User::factory()->cashier()->create();
        $service = Service::first();

        $this->actingAs($cashier)
            ->post(route('pos.store'), [
                'customer_id' => Customer::factory()->create()->id,
                'items' => [['service_id' => $service->id, 'quantity' => 1]],
                'shoe' => ['brand' => '', 'size' => ''],
            ])
            ->assertSessionHasErrors(['shoe.brand', 'shoe.size']);
    }

    public function test_staff_cannot_create_an_order(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->post(route('pos.store'), ['items' => [['service_id' => Service::first()->id, 'quantity' => 1]]])
            ->assertForbidden();
    }
}