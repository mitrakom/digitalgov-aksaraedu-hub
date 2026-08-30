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
            base_path('../../releases'),
            public_path('releases'),
        ];

        foreach ($searchPaths as $path) {
            if (File::isDirectory($path)) {
                $files = glob("{$path}/aksaraedu-lms-*.zip");
                if (empty($files)) {
                    $files = glob("{$path}/*.zip");
                }

                if (! empty($files)) {
                    // Get latest file by modification time
                    usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

                    return $files[0];
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
        $appPath = base_path('../app');
        if (! File::isDirectory($appPath)) {
            $appPath = base_path('../../app');
        }

        if (! File::isDirectory($appPath)) {
            $this->buildMinimalBaseZip($targetZipPath);

            return;
        }

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

        $excludedNames = [
            'node_modules', '.git', '.github', '.gitignore', '.gitattributes',
            '.agents', 'AGENTS.md', 'docs', 'scripts', 'releases', '.env', 'tests',
            'phpunit.xml', 'phpstan.neon', 'pint.json', 'eslint.config.js',
            '.prettierrc', '.prettierignore', '.editorconfig', 'tsconfig.json',
            'vite.config.ts', 'boost.json', 'package.json', 'package-lock.json',
            'bun.lock', 'pnpm-workspace.yaml', '.npmrc', '.fleet', '.vscode', '.idea',
            'storage/installed', 'storage/logs', 'storage/framework/cache/data',
            'storage/framework/sessions', 'storage/framework/views',
        ];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($appPath) + 1);

            $shouldExclude = false;
            foreach ($excludedNames as $exc) {
                if (str_starts_with($relativePath, $exc) || str_contains($relativePath, "/{$exc}")) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (! $shouldExclude) {
                $zip->addFile($filePath, $relativePath);
            }
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

        // 5. Inject root .htaccess for direct public_html extraction support
        $rootHtaccess = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
HTACCESS;
        $zip->addFromString('.htaccess', $rootHtaccess."\n");

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
}
