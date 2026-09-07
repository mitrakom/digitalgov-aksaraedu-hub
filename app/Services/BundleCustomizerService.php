<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lisensi;
use App\Models\RilisPembaruan;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class BundleCustomizerService
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {}

    /**
     * Generate a personalized client release zip bundle for a specific school license.
     *
     * @throws Exception
     */
    public function createCustomizedBundle(Lisensi $lisensi): string
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $lisensi->loadMissing('klienSekolah');
        $klien = $lisensi->klienSekolah;

        if (! $klien) {
            throw new Exception('Data klien sekolah untuk lisensi ini tidak ditemukan.');
        }

        // 1. Ensure signed license payload exists
        if (empty($lisensi->signed_license_payload)) {
            $lisensi->signed_license_payload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
            $lisensi->save();
        }

        // 2. Locate master release zip
        $masterZipPath = $this->findMasterReleaseZip();

        // 3. Prepare temporary destination file
        $tempDir = storage_path('app/temp_bundles');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $cleanNpsn = preg_replace('/[^A-Za-z0-9_-]/', '', $klien->npsn);
        $tempZipPath = "{$tempDir}/aksaraedu-lms-{$cleanNpsn}-custom-".uniqid().'.zip';

        if ($masterZipPath && File::exists($masterZipPath)) {
            File::copy($masterZipPath, $tempZipPath);
        } else {
            // Build zip dynamically from app source if master zip is not found
            $this->buildZipFromAppDirectory($tempZipPath);
        }

        // 4. Inject personalized files into the zip
        $this->injectPersonalizedFiles($tempZipPath, $lisensi);

        return $tempZipPath;
    }

    /**
     * Find latest master release zip in releases directory or database registry.
     */
    protected function findMasterReleaseZip(): ?string
    {
        // 1. Check from registered database releases
        try {
            $latestDbRelease = RilisPembaruan::whereNotNull('file_path_zip')
                ->latest('published_at')
                ->first();

            if ($latestDbRelease && ! empty($latestDbRelease->file_path_zip)) {
                $possibleDbPaths = [
                    Storage::disk('local')->path($latestDbRelease->file_path_zip),
                    storage_path('app/'.$latestDbRelease->file_path_zip),
                    storage_path('app/private/'.$latestDbRelease->file_path_zip),
                    public_path('releases/'.basename($latestDbRelease->file_path_zip)),
                ];

                foreach ($possibleDbPaths as $fullPath) {
                    if (File::exists($fullPath) && is_file($fullPath)) {
                        return $fullPath;
                    }
                }
            }
        } catch (Throwable) {
            // Abaikan jika database belum siap
        }

        // 2. Scan physical directories for release zip files
        $searchPaths = [
            storage_path('app/releases'),
            storage_path('app/private/releases'),
            base_path('releases'),
            base_path('../releases'),
            base_path('../app/releases'),
            base_path('../../releases'),
            base_path('../../app/releases'),
            public_path('releases'),
        ];

        foreach ($searchPaths as $path) {
            if (File::isDirectory($path)) {
                $files = glob("{$path}/aksaraedu-lms-*.zip");
                if (empty($files)) {
                    $files = glob("{$path}/*.zip");
                }

                if (! empty($files)) {
                    // Filter out custom or temporary bundle files
                    $files = array_filter($files, fn ($f) => ! str_contains(basename($f), '-custom-') && ! str_contains(basename($f), '-siap-pasang'));

                    if (! empty($files)) {
                        // Get latest file by modification time
                        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

                        return array_values($files)[0];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Build zip bundle directly from app directory if pre-packaged zip is missing.
     */
    protected function buildZipFromAppDirectory(string $targetZipPath): void
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $appPath = base_path('../app');
        if (! File::isDirectory($appPath)) {
            $appPath = base_path('../../app');
        }

        if (! File::isDirectory($appPath)) {
            $this->buildMinimalBaseZip($targetZipPath);

            return;
        }

        // 1. Coba gunakan zip binary sistem untuk kecepatan maksimal
        $zipBinary = trim((string) shell_exec('which zip 2>/dev/null'));
        if (! empty($zipBinary) && is_executable($zipBinary)) {
            $excludePatterns = [
                'node_modules/*',
                '.git/*',
                '.github/*',
                '.agents/*',
                'tests/*',
                'docs/*',
                'scripts/*',
                'releases/*',
                '.fleet/*',
                '.vscode/*',
                '.idea/*',
                'storage/logs/*',
                'storage/installed',
                'storage/framework/cache/data/*',
                'storage/framework/sessions/*',
                'storage/framework/views/*',
            ];
            $excludeArgs = implode(' -x ', array_map('escapeshellarg', $excludePatterns));
            $escapedTarget = escapeshellarg($targetZipPath);
            $escapedApp = escapeshellarg($appPath);

            $cmd = "cd {$escapedApp} && {$zipBinary} -r -q {$escapedTarget} . -x {$excludeArgs}";
            shell_exec($cmd);

            if (File::exists($targetZipPath) && filesize($targetZipPath) > 1000) {
                // Simpan cache master base zip agar tidak perlu kompilasi ulang
                $cachePath = storage_path('app/releases/aksaraedu-lms-latest-base.zip');
                if (! File::exists(dirname($cachePath))) {
                    File::makeDirectory(dirname($cachePath), 0755, true);
                }
                @copy($targetZipPath, $cachePath);

                return;
            }
        }

        // 2. Fallback menggunakan PHP ZipArchive dengan callback filter pruning direktori
        $zip = new ZipArchive;
        if ($zip->open($targetZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Gagal membuat berkas ZIP sementara di server.');
        }

        // Add essential directories
        $zip->addEmptyDir('storage/app/public');
        $zip->addEmptyDir('storage/framework/cache/data');
        $zip->addEmptyDir('storage/framework/sessions');
        $zip->addEmptyDir('storage/framework/views');
        $zip->addEmptyDir('storage/logs');
        $zip->addEmptyDir('storage/license');
        $zip->addEmptyDir('storage/keys');
        $zip->addEmptyDir('bootstrap/cache');

        $excludedDirs = [
            'node_modules', '.git', '.github', '.agents', 'docs', 'scripts',
            'releases', 'tests', '.fleet', '.vscode', '.idea',
        ];

        $excludedFiles = [
            '.env', 'phpunit.xml', 'phpstan.neon', 'pint.json', 'eslint.config.js',
            '.prettierrc', '.prettierignore', '.editorconfig', 'tsconfig.json',
            'vite.config.ts', 'boost.json', 'package.json', 'package-lock.json',
            'bun.lock', 'pnpm-workspace.yaml', '.npmrc', 'AGENTS.md',
        ];

        $filter = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($appPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            function ($current) use ($excludedDirs) {
                if ($current->isDir()) {
                    return ! in_array($current->getFilename(), $excludedDirs, true);
                }

                return true;
            }
        );

        $files = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($appPath) + 1);

            if (in_array(basename($relativePath), $excludedFiles, true)) {
                continue;
            }

            if (str_starts_with($relativePath, 'storage/framework/') || str_starts_with($relativePath, 'storage/logs/')) {
                continue;
            }

            $zip->addFile($filePath, $relativePath);
        }

        $zip->close();
    }

    /**
     * Inject custom license, public key, and pre-generated .env into the zip.
     */
    protected function injectPersonalizedFiles(string $zipPath, Lisensi $lisensi): void
    {
        $klien = $lisensi->klienSekolah;
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new Exception('Gagal memproses berkas ZIP rilis.');
        }

        // 1. Ensure storage subdirectories exist in ZIP
        $zip->addEmptyDir('storage/license');
        $zip->addEmptyDir('storage/keys');
        $zip->addEmptyDir('storage/framework/sessions');
        $zip->addEmptyDir('storage/framework/cache/data');
        $zip->addEmptyDir('storage/framework/views');
        $zip->addEmptyDir('storage/logs');
        $zip->addEmptyDir('bootstrap/cache');

        // Delete installed lock if present
        $zip->deleteName('storage/installed');

        // 2. Create and inject aksaraedu.lic
        $licensePayload = json_encode([
            'aksaraedu_license_file' => 'v1.0',
            'nomor_lisensi' => $lisensi->nomor_lisensi,
            'npsn' => $klien->npsn,
            'nama_sekolah' => $klien->nama_sekolah,
            'tipe_sekolah' => $klien->tipe_sekolah ?? 'smk',
            'model_lisensi' => $lisensi->model_lisensi,
            'tier_paket' => $lisensi->tier_paket,
            'provinsi' => $klien->provinsi,
            'kabupaten_kota' => $klien->kabupaten_kota,
            'alamat' => $klien->alamat_lengkap,
            'email_sekolah' => $klien->email_pic,
            'nomor_telepon' => $klien->kontak_pic_wa,
            'admin_nama' => $klien->nama_pic,
            'admin_email' => $klien->email_pic,
            'admin_phone' => $klien->kontak_pic_wa,
            'domain_terdaftar' => $lisensi->domain_terdaftar,
            'signed_package' => $lisensi->signed_license_payload,
            'public_key' => $this->licenseSigner->getPublicKey(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $zip->addFromString('storage/license/aksaraedu.lic', $licensePayload);
        $zip->addFromString('storage/license/.gitkeep', "# AksaraEdu LMS License Storage\n");

        // 3. Inject license_public.key
        $publicKey = $this->licenseSigner->getPublicKey();
        $zip->addFromString('storage/keys/license_public.key', $publicKey);
        $zip->addFromString('storage/keys/.gitkeep', "# AksaraEdu LMS Public Keys\n");

        // 4. Generate unique 256-bit APP_KEY
        $appKey = 'base64:'.base64_encode(random_bytes(32));
        $cleanSchoolName = addslashes($klien->nama_sekolah);

        $envContent = <<<ENV
APP_NAME="AksaraEdu LMS - {$cleanSchoolName}"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL=http://localhost

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Konfigurasi Basis Data Standar (Dapat disesuaikan via Web Installer /install)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aksaraedu_lms
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache Safe Fallback (File driver agar installer lancar sebelum migrasi DB)
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@aksaraedu.id"
MAIL_FROM_NAME="AksaraEdu LMS - {$cleanSchoolName}"

# Post-Deployment Webhook Secret Token
DEPLOY_WEBHOOK_SECRET=

VITE_APP_NAME="AksaraEdu LMS"
ENV;

        $zip->addFromString('.env', $envContent."\n");
        $zip->addFromString('.env.example', $envContent."\n");

        // 5. Inject root .htaccess for direct public_html / /app extraction support (Solusi 1)
        $rootHtaccess = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # 1. Proteksi Berkas & Direktori Sensitif
    RewriteRule ^(\.env|\.git|storage/logs/|storage/license/|composer\.(json|lock)|artisan|package\.(json|lock)|bun\.lock) - [F,L,NC]

    # 2. Teruskan traffic ke folder public/ jika belum di dalam public/
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS;
        $zip->addFromString('.htaccess', $rootHtaccess."\n");

        // 6. Inject root index.php proxy bridge for fallback (Solusi 1)
        $rootIndexPhp = <<<'PHP'
<?php
/**
 * AksaraEdu LMS - Root Proxy Bridge
 * Memastikan aplikasi berjalan normal jika DocumentRoot diarahkan ke folder utama proyek (/app).
 */
$publicIndex = __DIR__ . '/public/index.php';
if (file_exists($publicIndex)) {
    require_once $publicIndex;
} else {
    http_response_code(500);
    echo "Peringatan: Berkas public/index.php tidak ditemukan.";
}
PHP;
        $zip->addFromString('index.php', $rootIndexPhp."\n");

        $zip->close();
    }

    /**
     * Build minimal base zip structure when master zip / app directory is not accessible.
     */
    protected function buildMinimalBaseZip(string $targetZipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($targetZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Gagal membuat berkas ZIP sementara di server.');
        }

        $zip->addEmptyDir('app');
        $zip->addEmptyDir('bootstrap/cache');
        $zip->addEmptyDir('config');
        $zip->addEmptyDir('database');
        $zip->addEmptyDir('public/build');
        $zip->addEmptyDir('resources');
        $zip->addEmptyDir('routes');
        $zip->addEmptyDir('storage/app/public');
        $zip->addEmptyDir('storage/framework/cache/data');
        $zip->addEmptyDir('storage/framework/sessions');
        $zip->addEmptyDir('storage/framework/views');
        $zip->addEmptyDir('storage/logs');
        $zip->addEmptyDir('storage/license');
        $zip->addEmptyDir('storage/keys');

        $indexPhp = <<<'PHP'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
PHP;
        $zip->addFromString('public/index.php', $indexPhp);

        $versionJson = json_encode([
            'name' => 'AksaraEdu LMS',
            'version' => '1.0.0',
            'channel' => 'production',
            'build_at' => date('c'),
        ], JSON_PRETTY_PRINT);
        $zip->addFromString('version.json', $versionJson);

        $zip->close();
    }

    /**
     * Generate a signed, time-limited token for direct bundle download.
     */
    public function generateProvisionToken(Lisensi $lisensi, int $expiryHours = 48): string
    {
        $payload = [
            'lisensi_id' => $lisensi->id,
            'npsn' => $lisensi->klienSekolah?->npsn,
            'exp' => time() + ($expiryHours * 3600),
            'nonce' => bin2hex(random_bytes(8)),
        ];

        $json = json_encode($payload);
        $base64Payload = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $secretKey = config('app.key') ?: 'aksaraedu-hub-secret-key-2026';
        $signature = hash_hmac('sha256', $base64Payload, $secretKey);

        return "{$base64Payload}.{$signature}";
    }

    /**
     * Verify provision token and return corresponding Lisensi.
     */
    public function verifyProvisionToken(string $token): ?Lisensi
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$base64Payload, $signature] = $parts;
        $secretKey = config('app.key') ?: 'aksaraedu-hub-secret-key-2026';
        $expectedSignature = hash_hmac('sha256', $base64Payload, $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $remainder = strlen($base64Payload) % 4;
        if ($remainder) {
            $base64Payload .= str_repeat('=', 4 - $remainder);
        }

        $json = base64_decode(strtr($base64Payload, '-_', '+/'));
        if (! $json) {
            return null;
        }

        $payload = json_decode($json, true);
        if (! is_array($payload) || empty($payload['lisensi_id']) || empty($payload['exp'])) {
            return null;
        }

        if (time() > (int) $payload['exp']) {
            return null;
        }

        return Lisensi::with('klienSekolah')->find($payload['lisensi_id']);
    }

    /**
     * Generate standalone Single-File Web Bootstrap Loader (aksara-loader.php).
     */
    public function generateWebLoaderScript(Lisensi $lisensi): string
    {
        $token = $this->generateProvisionToken($lisensi, 48);
        $hubUrl = rtrim(config('app.url') ?? 'http://localhost', '/');
        $downloadUrl = "{$hubUrl}/api/v1/provision/download-bundle/{$token}";
        $schoolName = $lisensi->klienSekolah?->nama_sekolah ?? 'Sekolah Klien AksaraEdu';
        $npsn = $lisensi->klienSekolah?->npsn ?? '-';
        $licenseNo = $lisensi->nomor_lisensi ?? '-';
        $licenseModel = strtoupper($lisensi->model_lisensi ?? 'BELI_PUTUS');
        $expiryHuman = date('d M Y H:i', time() + (48 * 3600)).' WIB';

        $template = <<<'PHP_TEMPLATE'
<?php
/**
 * AksaraEdu LMS - Single-File Web Bootstrap Loader
 *
 * Generated automatically by AksaraEdu Central Hub.
 * Dedicated for: {{SCHOOL_NAME}} (NPSN: {{NPSN}})
 * Lisensi: {{LICENSE_NO}} [{{LICENSE_MODEL}}]
 * Expiry: {{EXPIRY_HUMAN}}
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '0');
@set_time_limit(600);
@ini_set('memory_limit', '512M');

define('AKSARAHUB_DOWNLOAD_URL', '{{DOWNLOAD_URL}}');
define('AKSARAHUB_SCHOOL_NAME', '{{SCHOOL_NAME}}');
define('AKSARAHUB_NPSN', '{{NPSN}}');
define('AKSARAHUB_LICENSE_NO', '{{LICENSE_NO}}');
define('AKSARAHUB_LICENSE_MODEL', '{{LICENSE_MODEL}}');
define('AKSARAHUB_EXPIRY', '{{EXPIRY_HUMAN}}');

// -------------------------------------------------------------
// 1. Diagnostic Checker
// -------------------------------------------------------------
function aksara_check_system(): array {
    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, '8.3.0', '>=');
    $zipOk = extension_loaded('zip') && class_exists('ZipArchive');
    $curlOk = extension_loaded('curl') || (bool) ini_get('allow_url_fopen');
    $writableOk = is_writable(__DIR__);

    return [
        'php_version' => $phpVersion,
        'php_ok' => $phpOk,
        'zip_ok' => $zipOk,
        'curl_ok' => $curlOk,
        'writable_ok' => $writableOk,
        'all_passed' => $phpOk && $zipOk && $curlOk && $writableOk,
        'target_dir' => __DIR__,
    ];
}

// -------------------------------------------------------------
// 2. Download Helper (cURL with fallback to fopen)
// -------------------------------------------------------------
function aksara_download_bundle(string $url, string $destPath): array {
    if (extension_loaded('curl')) {
        $fp = fopen($destPath, 'w+');
        if (! $fp) {
            return ['success' => false, 'error' => 'Gagal membuka berkas sementara untuk penulisan.'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AksaraEdu-WebLoader/1.0');

        $executed = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (! $executed || $httpCode !== 200) {
            @unlink($destPath);
            return [
                'success' => false,
                'error' => "Gagal mengunduh paket dari Hub (HTTP {$httpCode}): " . ($curlError ?: 'Respons tidak valid atau token kedaluwarsa.'),
            ];
        }

        if (filesize($destPath) < 1000) {
            $content = @file_get_contents($destPath);
            @unlink($destPath);
            return [
                'success' => false,
                'error' => "Berkas rilis tidak valid: " . substr($content, 0, 200),
            ];
        }

        return ['success' => true];
    }

    if (ini_get('allow_url_fopen')) {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: AksaraEdu-WebLoader/1.0\r\n",
                'timeout' => 600,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        $context = stream_context_create($opts);
        $data = @file_get_contents($url, false, $context);

        if ($data === false || strlen($data) < 1000) {
            return ['success' => false, 'error' => 'Gagal mengunduh via file_get_contents atau berkas terlalu kecil.'];
        }

        if (file_put_contents($destPath, $data) === false) {
            return ['success' => false, 'error' => 'Gagal menulis berkas sementara.'];
        }

        return ['success' => true];
    }

    return ['success' => false, 'error' => 'Server hosting tidak memiliki ekstensi cURL dan allow_url_fopen dinonaktifkan.'];
}

// -------------------------------------------------------------
// 3. Extraction & Security Setup (Solusi 1: Root Proxy & Protect)
// -------------------------------------------------------------
function aksara_extract_and_setup(string $zipPath, string $targetDir): array {
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return ['success' => false, 'error' => 'Gagal membuka arsip ZIP rilis yang diunduh.'];
    }

    if (! $zip->extractTo($targetDir)) {
        $zip->close();
        return ['success' => false, 'error' => 'Gagal mengekstrak berkas ke direktori sasaran. Periksa kuota disk dan izin tulis direktori.'];
    }
    $zip->close();

    // 1. Pastikan folder writable
    $writableDirs = ['storage', 'storage/app', 'storage/framework', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'];
    foreach ($writableDirs as $sub) {
        $p = $targetDir . '/' . $sub;
        if (! is_dir($p)) {
            @mkdir($p, 0775, true);
        }
        @chmod($p, 0775);
    }

    // 2. Pastikan Root .htaccess (Solusi 1: Strict Security & URL Rewrite ke public/)
    $htaccessPath = $targetDir . '/.htaccess';
    $htaccessContent = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # 1. Proteksi Berkas & Direktori Sensitif (Solusi 1)
    RewriteRule ^(\.env|\.git|storage/logs/|storage/license/|composer\.(json|lock)|artisan|package\.(json|lock)|bun\.lock) - [F,L,NC]

    # 2. Teruskan semua traffic ke folder public/
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
HTACCESS;
    @file_put_contents($htaccessPath, $htaccessContent);
    @chmod($htaccessPath, 0644);

    // 3. Pastikan Root index.php (Solusi 1: Proxy Bridge)
    $rootIndexPath = $targetDir . '/index.php';
    $rootIndexContent = <<<'PHP_BRIDGE'
<?php
/**
 * AksaraEdu LMS - Root Proxy Bridge
 * Memastikan aplikasi berjalan normal jika DocumentRoot diarahkan ke direktori proyek utama.
 */
$publicIndex = __DIR__ . '/public/index.php';
if (file_exists($publicIndex)) {
    require_once $publicIndex;
} else {
    http_response_code(500);
    echo "Peringatan: Berkas public/index.php tidak ditemukan.";
}
PHP_BRIDGE;
    @file_put_contents($rootIndexPath, $rootIndexContent);
    @chmod($rootIndexPath, 0644);

    return ['success' => true];
}

// -------------------------------------------------------------
// 4. API Endpoints Handler
// -------------------------------------------------------------
$action = $_REQUEST['action'] ?? '';

if ($action === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(aksara_check_system());
    exit;
}

if ($action === 'install') {
    header('Content-Type: application/json; charset=utf-8');
    $sys = aksara_check_system();
    if (! $sys['all_passed']) {
        echo json_encode([
            'success' => false,
            'error' => 'Prasyarat sistem belum terpenuhi. Pastikan PHP >= 8.3, ZipArchive aktif, dan direktori memiliki izin tulis.',
        ]);
        exit;
    }

    $tempZip = __DIR__ . '/aksara_bundle_' . uniqid() . '.zip';

    // Step 1: Download
    $dlResult = aksara_download_bundle(AKSARAHUB_DOWNLOAD_URL, $tempZip);
    if (! $dlResult['success']) {
        echo json_encode($dlResult);
        exit;
    }

    // Step 2: Extract & Setup
    $setupResult = aksara_extract_and_setup($tempZip, __DIR__);
    @unlink($tempZip);

    if (! $setupResult['success']) {
        echo json_encode($setupResult);
        exit;
    }

    // Step 3: Self-Destruct Loader File for Security
    @unlink(__FILE__);

    echo json_encode([
        'success' => true,
        'message' => 'Paket AksaraEdu LMS berhasil dipasang dan dikonfigurasi.',
        'redirect' => 'install',
    ]);
    exit;
}

// -------------------------------------------------------------
// 5. Interactive Modern Web UI
// -------------------------------------------------------------
$sys = aksara_check_system();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AksaraEdu LMS - Web Bootstrap Loader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-indigo-500 selection:text-white">
    <div class="w-full max-w-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 space-y-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-5">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-bold text-xl">
                    A
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight">AksaraEdu Web Loader</h1>
                    <p class="text-xs text-slate-400">Pemasang Mandiri Instan Klien Sekolah</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                Solusi 1 (Root Proxy)
            </span>
        </div>

        <!-- Detail Sekolah Target -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
            <div>
                <span class="text-slate-500 block uppercase font-medium tracking-wider">Sekolah Klien</span>
                <span class="font-semibold text-slate-200 text-sm"><?= htmlspecialchars(AKSARAHUB_SCHOOL_NAME) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block uppercase font-medium tracking-wider">NPSN / Lisensi</span>
                <span class="font-semibold text-slate-200"><?= htmlspecialchars(AKSARAHUB_NPSN) ?> &bull; <?= htmlspecialchars(AKSARAHUB_LICENSE_NO) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block uppercase font-medium tracking-wider">Tipe Lisensi</span>
                <span class="font-semibold text-emerald-400"><?= htmlspecialchars(AKSARAHUB_LICENSE_MODEL) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block uppercase font-medium tracking-wider">Berlaku Hingga</span>
                <span class="font-semibold text-slate-400"><?= htmlspecialchars(AKSARAHUB_EXPIRY) ?></span>
            </div>
        </div>

        <!-- Prasyarat Lingkungan Server -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Uji Prasyarat Lingkungan Hosting</h2>
            <div class="grid grid-cols-2 gap-2 text-xs">
                
                <div class="p-3 rounded-lg border flex items-center justify-between <?= $sys['php_ok'] ? 'bg-emerald-950/20 border-emerald-800/40 text-emerald-300' : 'bg-rose-950/20 border-rose-800/40 text-rose-300' ?>">
                    <span>PHP &ge; 8.3 (v<?= $sys['php_version'] ?>)</span>
                    <span class="font-bold"><?= $sys['php_ok'] ? '&#10003; Sah' : '&#10007; Butuh 8.3+' ?></span>
                </div>

                <div class="p-3 rounded-lg border flex items-center justify-between <?= $sys['zip_ok'] ? 'bg-emerald-950/20 border-emerald-800/40 text-emerald-300' : 'bg-rose-950/20 border-rose-800/40 text-rose-300' ?>">
                    <span>Ekstensi ZipArchive</span>
                    <span class="font-bold"><?= $sys['zip_ok'] ? '&#10003; Aktif' : '&#10007; Nonaktif' ?></span>
                </div>

                <div class="p-3 rounded-lg border flex items-center justify-between <?= $sys['curl_ok'] ? 'bg-emerald-950/20 border-emerald-800/40 text-emerald-300' : 'bg-rose-950/20 border-rose-800/40 text-rose-300' ?>">
                    <span>cURL / Remote Stream</span>
                    <span class="font-bold"><?= $sys['curl_ok'] ? '&#10003; Aktif' : '&#10007; Nonaktif' ?></span>
                </div>

                <div class="p-3 rounded-lg border flex items-center justify-between <?= $sys['writable_ok'] ? 'bg-emerald-950/20 border-emerald-800/40 text-emerald-300' : 'bg-rose-950/20 border-rose-800/40 text-rose-300' ?>">
                    <span>Izin Tulis Folder</span>
                    <span class="font-bold"><?= $sys['writable_ok'] ? '&#10003; Writable' : '&#10007; Read-Only' ?></span>
                </div>
            </div>
        </div>

        <!-- Progress Box (Hidden initially) -->
        <div id="progressArea" class="hidden space-y-3">
            <div class="flex justify-between text-xs font-semibold">
                <span id="progressStatus" class="text-indigo-400">Mempersiapkan pengunduhan...</span>
                <span id="progressPercent" class="text-slate-400">0%</span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                <div id="progressBar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
            </div>
            <!-- Log Console -->
            <div id="logConsole" class="bg-slate-950 p-3 rounded-lg font-mono text-[11px] text-slate-400 h-24 overflow-y-auto space-y-1 border border-slate-800">
                <div>[INFO] Memulai Web Bootstrap Loader...</div>
            </div>
        </div>

        <!-- Alert Error -->
        <div id="errorAlert" class="hidden p-4 rounded-xl bg-rose-950/30 border border-rose-800/50 text-rose-300 text-xs leading-relaxed">
            <strong class="font-bold block mb-1">Gagal Menjalankan Pemasangan:</strong>
            <span id="errorMessage"></span>
        </div>

        <!-- Action Button -->
        <div class="pt-2">
            <?php if ($sys['all_passed']): ?>
                <button id="btnStart" onclick="startInstallation()" class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Mulai Unduh &amp; Pasang Otomatis</span>
                </button>
            <?php else: ?>
                <div class="p-3.5 rounded-xl bg-amber-950/30 border border-amber-800/50 text-amber-300 text-xs text-center font-medium">
                    Prasyarat server belum lengkap. Mohon aktifkan ekstensi PHP yang dibutuhkan pada cPanel/hosting sebelum melanjutkan.
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="text-center text-[11px] text-slate-500 pt-2 border-t border-slate-800">
            AksaraEdu Ecosystem &bull; Single-File Zero-Touch Deployment &bull; Solusi 1
        </div>
    </div>

    <script>
        function log(msg) {
            const consoleEl = document.getElementById('logConsole');
            const row = document.createElement('div');
            row.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
            consoleEl.appendChild(row);
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }

        function setProgress(pct, status) {
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPercent').textContent = pct + '%';
            if (status) {
                document.getElementById('progressStatus').textContent = status;
                log(status);
            }
        }

        async function startInstallation() {
            const btn = document.getElementById('btnStart');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            
            document.getElementById('progressArea').classList.remove('hidden');
            document.getElementById('errorAlert').classList.add('hidden');

            setProgress(15, 'Menghubungi Central Hub Vendor...');

            try {
                setProgress(35, 'Mengunduh paket bundle aplikasi (server-to-server)...');
                
                const response = await fetch('?action=install', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Terjadi kesalahan saat memproses pemasangan.');
                }

                setProgress(80, 'Mengekstrak paket rilis & memasang konfigurasi keamanan root...');
                await new Promise(r => setTimeout(r, 600));

                setProgress(100, 'Pemasangan berhasil! Mengalihkan ke Wizard Installer...');
                log('Loader telah dibersihkan secara otomatis demi keamanan.');

                setTimeout(() => {
                    window.location.href = data.redirect || 'install';
                }, 1500);

            } catch (err) {
                document.getElementById('errorAlert').classList.remove('hidden');
                document.getElementById('errorMessage').textContent = err.message;
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                setProgress(0, 'Pemasangan terhenti karena kendala server.');
                log('ERROR: ' + err.message);
            }
        }
    </script>
</body>
</html>
PHP_TEMPLATE;

        $replacements = [
            '{{DOWNLOAD_URL}}' => addslashes($downloadUrl),
            '{{SCHOOL_NAME}}' => addslashes($schoolName),
            '{{NPSN}}' => addslashes($npsn),
            '{{LICENSE_NO}}' => addslashes($licenseNo),
            '{{LICENSE_MODEL}}' => addslashes($licenseModel),
            '{{EXPIRY_HUMAN}}' => addslashes($expiryHuman),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}


