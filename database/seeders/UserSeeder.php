<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Development accounts, one per role.
 *
 * These credentials are for local use only and must be changed before any
 * real deployment; passwords are hashed through the User model cast.
 */
class UserSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, email: string, role: UserRole, password: string}>
     */
    private const ACCOUNTS = [
        [
            'name' => 'Dewi (Admin)',
            'email' => 'admin@ohmywash.test',
            'role' => UserRole::Admin,
            'password' => 'admin123',
        ],
        [
            'name' => 'Rizky (Kasir)',
            'email' => 'kasir@ohmywash.test',
            'role' => UserRole::Cashier,
            'password' => 'kasir123',
        ],
        [
            'name' => 'Bima (Staff Laundry)',
            'email' => 'staff@ohmywash.test',
            'role' => UserRole::Staff,
            'password' => 'staff123',
        ],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => Hash::make($account['password']),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}