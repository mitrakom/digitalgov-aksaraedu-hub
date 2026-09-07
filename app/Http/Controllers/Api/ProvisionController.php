<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BundleCustomizerService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProvisionController extends Controller
{
    /**
     * Unduh paket bundle kustom menggunakan token provisi aman berbatas waktu.
     * GET /api/v1/provision/download-bundle/{token}
     */
    public function downloadBundle(string $token, BundleCustomizerService $bundleCustomizer): BinaryFileResponse|JsonResponse
    {
        $lisensi = $bundleCustomizer->verifyProvisionToken($token);

        if (! $lisensi) {
            return response()->json([
                'success' => false,
                'error' => 'Token provisi tidak sah, salah tanda tangan, atau sudah kedaluwarsa (48 jam). Mohon unduh kembali berkas loader dari portal Central Hub.',
            ], 403);
        }

        try {
            $zipPath = $bundleCustomizer->createCustomizedBundle($lisensi);
            $cleanNpsn = preg_replace('/[^A-Za-z0-9_-]/', '', $lisensi->klienSekolah->npsn ?? 'client');
            $filename = "aksaraedu-lms-{$cleanNpsn}-bundle.zip";

            return response()->download($zipPath, $filename, [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menyiapkan paket bundle kustom: '.$e->getMessage(),
            ], 500);
        }
    }
}
