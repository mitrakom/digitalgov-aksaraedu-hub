<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lisensi;
use App\Models\RilisPembaruan;
use App\Models\RiwayatUpdate;
use App\Services\LicenseSignerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UpdateApiController extends Controller
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {
    }

    /**
     * Cek Update Registry (GET /api/v1/updates/check)
     */
    public function check(Request $request): JsonResponse
    {
        $currentVersion = $request->query('current_version', '1.0.0');
        $npsn = $request->query('npsn');
        $token = $request->query('token') ?: $request->bearerToken();

        $lisensi = null;
        if ($token) {
            $lisensi = Lisensi::with('klienSekolah')->where('token_api', $token)->first();
        }

        $latestRelease = RilisPembaruan::where('is_public', true)
            ->latest('published_at')
            ->first();

        if (! $latestRelease) {
            return response()->json([
                'update_available' => false,
                'message' => 'Sistem Anda sudah dalam versi terbaru.',
            ]);
        }

        $updateAvailable = version_compare($latestRelease->nomor_versi, $currentVersion, '>');

        if (! $updateAvailable) {
            return response()->json([
                'update_available' => false,
                'current_version' => $currentVersion,
                'latest_version' => $latestRelease->nomor_versi,
                'message' => 'Versi LMS saat ini sudah yang paling mutakhir.',
            ]);
        }

        $isCoveredByWarranty = false;
        if ($lisensi) {
            if ($lisensi->model_lisensi === 'langganan' && $lisensi->status === 'active') {
                $isCoveredByWarranty = true;
            } elseif ($lisensi->model_lisensi === 'beli_putus') {
                $isCoveredByWarranty = $latestRelease->tipe_rilis === 'patch_bugfix'
                    ? true
                    : $lisensi->isWarrantyActive();
            }
        }

        return response()->json([
            'update_available' => true,
            'version' => $latestRelease->nomor_versi,
            'release_type' => $latestRelease->tipe_rilis,
            'is_critical_patch' => $latestRelease->is_critical_patch,
            'is_covered_by_warranty' => $isCoveredByWarranty,
            'changelog' => $latestRelease->ringkasan_perubahan,
            'download_url' => url("/api/v1/updates/download/{$latestRelease->nomor_versi}?token={$token}"),
            'checksum_sha256' => $latestRelease->checksum_sha256,
            'file_signature' => $latestRelease->file_signature,
            'published_at' => $latestRelease->published_at?->toIso8601String(),
        ]);
    }

    /**
     * Unduh paket update (GET /api/v1/updates/download/{version})
     */
    public function download(Request $request, string $version): JsonResponse|BinaryFileResponse
    {
        $token = $request->query('token') ?: $request->bearerToken();
        if (! $token) {
            return response()->json(['error' => 'Token otentikasi lisensi dibutuhkan.'], 401);
        }

        $lisensi = Lisensi::where('token_api', $token)->first();
        if (! $lisensi || $lisensi->status === 'revoked') {
            return response()->json(['error' => 'Lisensi tidak sah atau telah dicabut.'], 403);
        }

        $release = RilisPembaruan::where('nomor_versi', $version)->first();
        if (! $release) {
            return response()->json(['error' => 'Versi rilis tidak ditemukan.'], 404);
        }

        // Catat Riwayat Update
        RiwayatUpdate::create([
            'rilis_pembaruan_id' => $release->id,
            'lisensi_id' => $lisensi->id,
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);

        if ($release->file_path_zip && file_exists(storage_path('app/'.$release->file_path_zip))) {
            return response()->download(storage_path('app/'.$release->file_path_zip));
        }

        return response()->json([
            'status' => 'success',
            'message' => "Paket rilis v{$version} siap di-sync via repository registry.",
            'checksum_sha256' => $release->checksum_sha256,
            'file_signature' => $release->file_signature,
        ]);
    }
}
