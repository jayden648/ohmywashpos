<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Bayar',
            self::Partial => 'Sebagian',
            self::Paid => 'Lunas',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-red-100 text-red-700 ring-red-200',
            self::Partial => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Paid => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::Refunded => 'bg-gray-100 text-gray-700 ring-gray-200',
        };
    }
}