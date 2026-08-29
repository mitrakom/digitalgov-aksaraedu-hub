<?php

namespace App\Services;

use App\Models\Lisensi;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LicenseSignerService
{
    protected string $keyPath;

    protected string $privateKeyFile;

    protected string $publicKeyFile;

    public function __construct()
    {
        $this->keyPath = storage_path('keys');
        $this->privateKeyFile = $this->keyPath.'/license_private.key';
        $this->publicKeyFile = $this->keyPath.'/license_public.key';
    }

    /**
     * Ensure RSA Keypair exists, generate if missing.
     */
    public function ensureKeysExist(): array
    {
        if (! File::exists($this->keyPath)) {
            File::makeDirectory($this->keyPath, 0700, true);
        }

        if (! File::exists($this->privateKeyFile) || ! File::exists($this->publicKeyFile)) {
            return $this->generateNewKeyPair();
        }

        return [
            'private_key' => File::get($this->privateKeyFile),
            'public_key' => File::get($this->publicKeyFile),
        ];
    }

    /**
     * Generate fresh RSA-4096 Keypair.
     */
    public function generateNewKeyPair(): array
    {
        if (! File::exists($this->keyPath)) {
            File::makeDirectory($this->keyPath, 0700, true);
        }

        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if (! $res) {
            throw new Exception('Gagal membuat kunci RSA: '.openssl_error_string());
        }

        openssl_pkey_export($res, $privateKey);
        $keyDetails = openssl_pkey_get_details($res);
        $publicKey = $keyDetails['key'];

        File::put($this->privateKeyFile, $privateKey);
        chmod($this->privateKeyFile, 0600);

        File::put($this->publicKeyFile, $publicKey);
        chmod($this->publicKeyFile, 0644);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    /**
     * Get Public Key string.
     */
    public function getPublicKey(): string
    {
        $this->ensureKeysExist();

        return File::get($this->publicKeyFile);
    }

    /**
     * Sign a structured license payload with RSA Private Key.
     */
    public function signPayload(array $payload): string
    {
        $this->ensureKeysExist();
        $privateKey = File::get($this->privateKeyFile);

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $signature = '';
        $success = openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $success) {
            throw new Exception('Gagal menandatangani payload lisensi: '.openssl_error_string());
        }

        // Package as format: base64(payload).base64(signature)
        $encodedPayload = base64_encode($jsonPayload);
        $encodedSignature = base64_encode($signature);

        return $encodedPayload.'.'.$encodedSignature;
    }

    /**
     * Verify license package using Public Key.
     */
    public function verifySignedPackage(string $signedPackage): array
    {
        $parts = explode('.', $signedPackage);
        if (count($parts) !== 2) {
            return ['valid' => false, 'error' => 'Format signed package tidak valid'];
        }

        [$encodedPayload, $encodedSignature] = $parts;
        $jsonPayload = base64_decode($encodedPayload);
        $signature = base64_decode($encodedSignature);

        $publicKey = $this->getPublicKey();
        $verifyResult = openssl_verify($jsonPayload, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($verifyResult === 1) {
            $payload = json_decode($jsonPayload, true);

            return ['valid' => true, 'payload' => $payload];
        }

        return ['valid' => false, 'error' => 'Signature verifikasi gagal'];
    }

    /**
     * Generate standard Serial Key for quick activation: AKSR-YYYY-TIPE-RANDOM-RANDOM
     */
    public function generateSerialKey(string $tipeSekolah = 'SMK'): string
    {
        $year = date('Y');
        $tipe = strtoupper($tipeSekolah);
        $part1 = strtoupper(Str::random(4));
        $part2 = strtoupper(Str::random(4));

        return "AKSR-{$year}-{$tipe}-{$part1}-{$part2}";
    }

    /**
     * Generate API Token for client sync.
     */
    public function generateApiToken(): string
    {
        return 'aksr_live_'.bin2hex(random_bytes(32));
    }

    /**
     * Generate full Signed License file package for an active Lisensi model.
     */
    public function generateSignedLicensePayload(Lisensi $lisensi): string
    {
        $klien = $lisensi->klienSekolah;

        $payload = [
            'license_id' => $lisensi->id,
            'nomor_lisensi' => $lisensi->nomor_lisensi,
            'serial_key' => $lisensi->serial_key,
            'npsn' => $klien->npsn,
            'nama_sekolah' => $klien->nama_sekolah,
            'tipe_sekolah' => $klien->tipe_sekolah,
            'model_lisensi' => $lisensi->model_lisensi,
            'tier_paket' => $lisensi->tier_paket,
            'domain_terdaftar' => $lisensi->domain_terdaftar,
            'hardware_fingerprint' => $lisensi->hardware_fingerprint,
            'tanggal_rilis' => $lisensi->tanggal_rilis ? $lisensi->tanggal_rilis->toDateString() : null,
            'tanggal_kadaluarsa' => $lisensi->tanggal_kadaluarsa ? $lisensi->tanggal_kadaluarsa->toDateString() : null,
            'garansi_bugfix_hingga' => $lisensi->garansi_bugfix_hingga ? $lisensi->garansi_bugfix_hingga->toDateString() : null,
            'allowed_features' => $lisensi->allowed_features ?? [
                'cbt_engine',
                'kurikulum_merdeka',
                'multimedia_materials',
                'leger_nilai',
                'presensi_qr',
                'rapor_otomatis',
            ],
            'issuer' => 'AksaraEdu Central Hub Licensing Authority',
            'issued_at' => now()->toIso8601String(),
        ];

        return $this->signPayload($payload);
    }
}
