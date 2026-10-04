<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_newly_registered_users_are_staff_and_cannot_escalate(): void
    {
        $this->post('/register', [
            'name' => 'Walk In',
            'email' => 'walkin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // Attempted privilege escalation via self-registration:
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'walkin@example.com')->firstOrFail();

        $this->assertSame(UserRole::Staff, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_guests_are_redirected_from_admin_area(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_staff_and_cashier_cannot_reach_user_management(): void
    {
        foreach ([User::factory()->staff()->create(), User::factory()->cashier()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('admin.users.index'))
                ->assertForbidden();
        }
    }

    public function test_non_admin_cannot_create_or_edit_users(): void
    {
        $cashier = User::factory()->cashier()->create();
        $target = User::factory()->staff()->create();

        $this->actingAs($cashier)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->get(route('admin.users.edit', $target))
            ->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->staff()->create(['name' => 'Findable Person']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Findable Person');
    }

    public function test_admin_can_filter_users_by_search_and_role(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->cashier()->create(['name' => 'Zainab Cashier']);
        User::factory()->staff()->create(['name' => 'Hidden Person']);

        // The acting administrator's own name appears in the navigation bar,
        // so the negative assertions use other accounts instead.
        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Zainab']))
            ->assertOk()
            ->assertSee('Zainab Cashier')
            ->assertDontSee('Hidden Person');

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'staff']))
            ->assertOk()
            ->assertSee('Hidden Person')
            ->assertDontSee('Zainab Cashier');
    }

    public function test_admin_can_create_a_cashier(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Cashier',
                'email' => 'cashier@example.com',
                'password' => 'secret-password-123',
                'password_confirmation' => 'secret-password-123',
                'role' => UserRole::Cashier->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'cashier@example.com',
            'role' => UserRole::Cashier->value,
        ]);
    }

    public function test_creating_a_user_requires_a_valid_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Bad Role',
                'email' => 'bad@example.com',
                'password' => 'secret-password-123',
                'password_confirmation' => 'secret-password-123',
                'role' => 'superuser',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'bad@example.com']);
    }

    public function test_admin_can_change_another_users_role(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => UserRole::Cashier->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(UserRole::Cashier, $staff->fresh()->role);
    }

    public function test_admin_cannot_edit_their_own_account_via_this_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $admin))
            ->assertForbidden();
    }
    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create(); // ensure more than one admin exists

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_last_administrator_is_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        // The only administrator cannot delete themselves.
        $this->assertFalse($admin->can('delete', $admin));

        // A non-admin account can still be removed.
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_admin_can_delete_another_administrator_when_one_remains(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $other))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_an_admin_may_be_demoted_while_another_admin_remains(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $other), [
                'name' => $other->name,
                'email' => $other->email,
                'role' => UserRole::Staff->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        // Demoting one of two administrators is safe: one still remains.
        $this->assertSame(UserRole::Staff, $other->fresh()->role);
        $this->assertSame(1, User::adminCount());
    }

    public function test_role_helpers_and_enum_casts_work(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->cashier()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->hasRole(UserRole::Admin));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasAnyRole('staff', 'admin'));

        $this->assertTrue($cashier->isCashier());
        $this->assertFalse($cashier->isAdmin());
        $this->assertFalse($cashier->hasAnyRole('admin', 'staff'));
    }
}
