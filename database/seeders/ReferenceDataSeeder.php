<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

/**
 * Sample customers, the WELCOME10 promotion and starter inventory.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['C001', 'Gabriel', '081234567890', 'Surabaya', null],
            ['C002', 'Sinta Maharani', '081355500011', 'Sidoarjo', 'Suka sneaker putih'],
            ['C003', 'Andre Wijaya', '082144400022', 'Gresik', null],
        ];

        foreach ($customers as [$code, $name, $phone, $address, $notes]) {
            Customer::query()->updateOrCreate(
                ['customer_code' => $code],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                    'notes' => $notes,
                    'is_active' => true,
                ],
            );
        }

        // 10% off above Rp50.000, capped at Rp30.000.
        Promotion::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'name' => 'Welcome 10%',
                'description' => 'Diskon 10% untuk transaksi minimal Rp50.000, maksimal potongan Rp30.000.',
                'discount_percent' => 10,
                'minimum_total' => 50000,
                'maximum_discount' => 30000,
                'is_active' => true,
            ],
        );

        $inventory = [
            ['Cleaning Chemical', 'botol', 3, 5],
            ['Microfiber', 'pcs', 40, 10],
            ['Shoe Box', 'pcs', 8, 10],
        ];

        foreach ($inventory as [$name, $unit, $quantity, $minimum]) {
            InventoryItem::query()->updateOrCreate(
                ['name' => $name],
                [
                    'unit' => $unit,
                    'quantity' => $quantity,
                    'minimum_stock' => $minimum,
                    'is_active' => true,
                ],
            );
        }
    }
}