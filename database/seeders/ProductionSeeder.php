<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\LicenseSignerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production database with 1 initial Administrator account and master RSA keypair.
     */
    public function run(): void
    {
        // 1. Pastikan Master Keypair RSA-4096 tersedia
        $signer = app(LicenseSignerService::class);
        $signer->ensureKeysExist();

        // 2. Akun Tunggal Administrator Produksi (Idempotent)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '081234567890',
            ]
        );
    }
}
