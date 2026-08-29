<?php

namespace App\Console\Commands;

use App\Services\LicenseSignerService;
use Illuminate\Console\Command;

class GenerateLicenseKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate-keys {--force : Timpa kunci yang sudah ada jika tersedia}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA-4096 Keypair untuk master engine lisensi AksaraEdu';

    /**
     * Execute the console command.
     */
    public function handle(LicenseSignerService $signer): int
    {
        $this->info('Memeriksa pasangan kunci RSA AksaraEdu...');

        if ($this->option('force')) {
            $this->warn('Membuat ulang kunci RSA-4096 (Force overwrite)...');
            $signer->generateNewKeyPair();
            $this->info('Kunci RSA-4096 baru berhasil dibuat di storage/keys/.');
            return Command::SUCCESS;
        }

        $keys = $signer->ensureKeysExist();
        $this->info('Kunci RSA-4096 sudah siap dan tersimpan aman di storage/keys/.');

        return Command::SUCCESS;
    }
}
