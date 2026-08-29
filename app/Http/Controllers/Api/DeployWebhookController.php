<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DeployWebhookController extends Controller
{
    /**
     * Handle Post-Deploy Webhook:
     * 1. Validate Secret Token
     * 2. Auto-extract deploy.zip if found in base directory
     * 3. Run database migrations & cache optimizations
     */
    public function handle(Request $request): JsonResponse
    {
        $expectedToken = config('app.deploy_webhook_secret');

        if (empty($expectedToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'DEPLOY_WEBHOOK_SECRET belum dikonfigurasi di server.',
            ], 500);
        }

        $providedToken = $request->header('X-Deploy-Token')
            ?? $request->bearerToken()
            ?? $request->input('token');

        if (! hash_equals($expectedToken, (string) $providedToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak: Token autentikasi deploy webhook tidak valid.',
            ], 403);
        }

        $outputLog = [];
        $basePath = base_path();
        $zipFile = $basePath.'/deploy.zip';

        // 1. Ekstraksi deploy.zip jika ada
        if (File::exists($zipFile)) {
            try {
                $zip = new ZipArchive;
                if ($zip->open($zipFile) === true) {
                    $zip->extractTo($basePath);
                    $zip->close();
                    @unlink($zipFile);
                    $outputLog[] = '✓ deploy.zip berhasil diekstrak dan dibersihkan.';
                } else {
                    $outputLog[] = '⚠️ Gagal membuka deploy.zip.';
                }
            } catch (Exception $e) {
                $outputLog[] = '⚠️ Ekstraksi zip error: '.$e->getMessage();
            }
        } else {
            $outputLog[] = 'ℹ️ File deploy.zip tidak ditemukan (mungkin telah diekstrak sebelumnya).';
        }

        // 2. Pastikan direktori penting tersedia
        if (! File::exists(storage_path('keys'))) {
            File::makeDirectory(storage_path('keys'), 0700, true);
        }

        // 3. Database Migration
        try {
            Artisan::call('migrate', ['--force' => true]);
            $outputLog[] = '✓ php artisan migrate: '.trim(Artisan::output());
        } catch (Exception $e) {
            $outputLog[] = '⚠️ Migrasi gagal: '.$e->getMessage();
        }

        // 4. Cache Optimizations
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $outputLog[] = '✓ Optimasi cache (config, route, view) berhasil diterapkan.';
        } catch (Exception $e) {
            $outputLog[] = '⚠️ Cache optimization warning: '.$e->getMessage();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proses post-deploy webhook berhasil diselesaikan.',
            'timestamp' => now()->toIso8601String(),
            'logs' => $outputLog,
        ], 200);
    }
}
