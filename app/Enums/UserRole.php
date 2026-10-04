<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Cashier = 'cashier';
    case Staff = 'staff';

    /**
     * Human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Cashier => 'Cashier',
            self::Staff => 'Staff',
        };
    }

    /**
     * Roles, in order of descending privilege.
     *
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [self::Admin, self::Cashier, self::Staff];
    }
}