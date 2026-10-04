<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case BankTransfer = 'bank_transfer';
    case EWallet = 'ewallet';
    case DebitCreditCard = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Qris => 'QRIS',
            self::BankTransfer => 'Bank Transfer',
            self::EWallet => 'E-Wallet',
            self::DebitCreditCard => 'Debit/Credit Card',
        };
    }

    /**
     * Only cash needs the amount tendered so change can be calculated.
     */
    public function requiresTenderedAmount(): bool
    {
        return $this === self::Cash;
    }

    /**
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [
            self::Cash,
            self::Qris,
            self::BankTransfer,
            self::EWallet,
            self::DebitCreditCard,
        ];
    }
}