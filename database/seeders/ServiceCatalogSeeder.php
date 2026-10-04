<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds the OhMyWash service catalogue taken from the POS prototype.
 *
 * Prices are reference data only; authorised staff may edit them at any
 * time through the Layanan screen and nothing is hard coded in the UI.
 */
class ServiceCatalogSeeder extends Seeder
{
    /**
     * Category => [name, price, estimated days].
     *
     * @var array<string, array<int, array{string, int, int}>>
     */
    private const CATALOGUE = [
        'Cleaning' => [
            ['Sepatu Olahraga', 35000, 2],
            ['Sepatu Sneaker', 30000, 2],
            ['Sepatu Kulit', 30000, 2],
            ['Sepatu Hiking', 35000, 3],
            ['Sepatu Suede', 35000, 3],
            ['Helmet', 35000, 2],
        ],
        'Treatment' => [
            ['Premium Treatment / Cuci 3', 85000, 4],
            ['Unyellowing', 35000, 3],
        ],
        'Repair' => [
            ['Repaint (mulai)', 50000, 5],
        ],
        'Additional Service' => [
            ['Fast Service / Express', 25000, 1],
        ],
    ];

    public function run(): void
    {
        foreach (self::CATALOGUE as $categoryName => $services) {
            $category = ServiceCategory::query()->firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => match ($categoryName) {
                        'Cleaning' => 'Pencucian rutin untuk berbagai jenis alas kaki.',
                        'Treatment' => 'Perawatan intensif dengan treatment tambahan.',
                        'Repair' => 'Perbaikan dan perbaikan warna.',
                        'Additional Service' => 'Layanan tambahan untuk mempercepat proses.',
                        default => null,
                    },
                    'is_active' => true,
                ],
            );

            foreach ($services as [$name, $price, $days]) {
                Service::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'service_category_id' => $category->id,
                        'name' => $name,
                        'price' => $price,
                        'estimated_duration' => $days,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}