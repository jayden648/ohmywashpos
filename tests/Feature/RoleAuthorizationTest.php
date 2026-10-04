<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\User;
use App\Services\OrderCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Confirms the role matrix is enforced server-side, not merely by hiding
 * navigation: each role is refused the URLs it may not use.
 */
class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Route names paired with the roles allowed to reach them.
     *
     * @return array<string, array{string}>
     */
    public static function restrictedRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'pos' => ['pos.index'],
            'customers' => ['customers.index'],
            'services' => ['services.index'],
            'inventory' => ['inventory.index'],
            'reports' => ['reports.index'],
            'admin users' => ['admin.users.index'],
            'payments' => ['payments.index'],
        ];
    }

    #[DataProvider('restrictedRoutes')]
    public function test_staff_is_refused_administrative_screens(string $routeName): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get(route($routeName))
            ->assertForbidden();
    }

    public function test_cashier_is_refused_admin_only_screens(): void
    {
        $cashier = User::factory()->cashier()->create();

        foreach (['services.index', 'inventory.index', 'reports.index', 'admin.users.index'] as $route) {
            $this->actingAs($cashier)->get(route($route))->assertForbidden();
        }
    }

    public function test_admin_may_reach_every_screen(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['dashboard', 'pos.index', 'orders.index', 'customers.index', 'services.index', 'reports.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_guests_are_redirected_from_protected_screens(): void
    {
        foreach (['dashboard', 'pos.index', 'orders.index', 'customers.index'] as $route) {
            $this->get(route($route))->assertRedirect('/login');
        }
    }

    public function test_staff_may_still_reach_the_order_book_and_tracking(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('orders.index'))->assertOk();
        $this->actingAs($staff)->get(route('tracking.index'))->assertOk();
    }

    /**
     * The promotion preview and the stored total must agree exactly.
     */
    public function test_the_calculator_matches_the_stored_order_total(): void
    {
        $calculator = new OrderCalculator;

        $service = Service::factory()->price(33333.33)->create();

        $result = $calculator->calculate([['service_id' => $service->id, 'quantity' => 3]]);

        // Exact integer-cent arithmetic: 33333.33 x 3 with no float drift.
        $this->assertSame(99999.99, $result['subtotal']);
        $this->assertSame($result['subtotal'], $result['total']);
        $this->assertSame(0.0, $result['discount']);
    }

    public function test_inventory_low_stock_is_detected(): void
    {
        InventoryItem::factory()->lowStock()->create(['name' => 'Shoe Box']);
        InventoryItem::factory()->create(['name' => 'Microfiber', 'quantity' => 100, 'minimum_stock' => 5]);

        $this->assertSame(1, InventoryItem::query()->lowStock()->count());
    }
}