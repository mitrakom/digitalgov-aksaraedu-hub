<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * AksaraEdu Central Hub - Resilient Post-Deploy Webhook
 *
 * Standalone entry point to extract deploy.zip, migrate database,
 * and clear caches without dependency on full framework routing.
 */
header('Content-Type: application/json; charset=utf-8');

$baseDir = dirname(__DIR__);
$envFile = $baseDir.'/.env';

// 1. Baca DEPLOY_WEBHOOK_SECRET dari .env
$expectedSecret = null;
if (file_exists($envFile)) {
    $envContent = (string) file_get_contents($envFile);
    if (preg_match('/^DEPLOY_WEBHOOK_SECRET=(.*)$/m', $envContent, $matches)) {
        $expectedSecret = trim($matches[1], "\"' \t\n\r\0\x0B");
    }
}

if (empty($expectedSecret)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'DEPLOY_WEBHOOK_SECRET belum dikonfigurasi di file .env server.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// 2. Ambil token dari request (Header, Bearer, atau Body)
$rawBody = (string) file_get_contents('php://input');
$jsonBody = json_decode($rawBody, true) ?? [];

$receivedToken = $_SERVER['HTTP_X_DEPLOY_TOKEN']
    ?? (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $m) ? $m[1] : null)
    ?? ($jsonBody['token'] ?? null)
    ?? ($_POST['token'] ?? null)
    ?? ($_GET['token'] ?? null);

if (! $receivedToken || ! hash_equals((string) $expectedSecret, (string) $receivedToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak: Token deploy webhook tidak sah.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$logs = [];

// 3. Ekstraksi deploy.zip jika ditemukan
$zipPaths = [
    $baseDir.'/deploy.zip',
    __DIR__.'/deploy.zip',
];

$extracted = false;
foreach ($zipPaths as $zipPath) {
    if (file_exists($zipPath)) {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($baseDir);
            $zip->close();
            @unlink($zipPath);
            $logs[] = "✓ File deploy.zip berhasil diekstrak ke {$baseDir} dan dihapus.";
            $extracted = true;
            break;
        } else {
            $logs[] = "⚠️ Gagal membuka file zip: {$zipPath}";
        }
    }
}

if (! $extracted) {
    $logs[] = 'ℹ️ File deploy.zip tidak ditemukan (mungkin telah diekstrak sebelumnya).';
}

// 4. Pastikan direktori penting tersedia
$dirsToEnsure = [
    $baseDir.'/storage/framework/views',
    $baseDir.'/storage/framework/sessions',
    $baseDir.'/storage/framework/cache',
    $baseDir.'/storage/logs',
    $baseDir.'/storage/keys',
    $baseDir.'/bootstrap/cache',
];

foreach ($dirsToEnsure as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 5. Bootstrap Laravel Artisan untuk Migrate & Optimize Cache
try {
    if (file_exists($baseDir.'/vendor/autoload.php') && file_exists($baseDir.'/bootstrap/app.php')) {
        require_once $baseDir.'/vendor/autoload.php';
        /** @var Application $app */
        $app = require_once $baseDir.'/bootstrap/app.php';

        $kernel = $app->make(Kernel::class);

        // Run migrations & Seeder
        $kernel->call('migrate', ['--force' => true]);
        $logs[] = '✓ Database migrate: '.trim($kernel->output());

        $kernel->call('db:seed', ['--force' => true]);
        $logs[] = '✓ Database seed (Production initial admin & keys): '.trim($kernel->output());

        // Optimasi Cache
        $kernel->call('config:cache');
        $kernel->call('route:cache');
        $kernel->call('view:cache');
        $logs[] = '✓ Cache optimization (config, route, view) berhasil diterapkan.';
    } else {
        $logs[] = 'ℹ️ File vendor/autoload.php belum lengkap.';
    }
} catch (Throwable $e) {
    $logs[] = '⚠️ Artisan command warning: '.$e->getMessage();
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Proses post-deploy webhook AksaraEdu Central Hub selesai.',
    'timestamp' => date('c'),
    'logs' => $logs,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
