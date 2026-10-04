<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Safe to run repeatedly: every seeder matches on natural keys such as
     * the service slug or the promotion code, so existing rows are updated
     * rather than duplicated.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceCatalogSeeder::class,
            ReferenceDataSeeder::class,
        ]);
    }
}
