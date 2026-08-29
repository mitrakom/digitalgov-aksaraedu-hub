<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database based on environment.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            // Lingkungan Produksi: Hanya 1 akun admin resmi & inisialisasi RSA
            $this->call(ProductionSeeder::class);
        } else {
            // Lingkungan Lokal & Testing: Dataset lengkap untuk pengujian fitur
            $this->call(DevelopmentSeeder::class);
        }
    }
}
