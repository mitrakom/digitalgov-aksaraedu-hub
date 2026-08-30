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
    ) {}

    /**
     * Cek Update Registry (GET /api/v1/updates/check)
     */
    public function check(Request $request): JsonResponse
    {
        $currentVersion = (string) $request->query('current_version', '1.0.0');
        $npsn = (string) $request->query('npsn');
        $token = (string) ($request->query('token') ?: $request->bearerToken());

        $lisensi = null;
        if (! empty($token)) {
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

    /**
     * Publikasi Otomatis Rilis Baru via CI/CD (POST /api/v1/updates/publish)
     */
    public function publish(Request $request): JsonResponse
    {
        $expectedToken = config('app.deploy_webhook_secret');
        $providedToken = $request->header('X-Deploy-Token')
            ?? $request->bearerToken()
            ?? $request->input('token');

        if (empty($expectedToken) || ! hash_equals($expectedToken, (string) $providedToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak: Token autentikasi deploy webhook tidak valid.',
            ], 403);
        }

        $validated = $request->validate([
            'nomor_versi' => 'required|string|max:30',
            'tipe_rilis' => 'nullable|in:patch_bugfix,minor_feature,major_curriculum',
            'ringkasan_perubahan' => 'nullable|string',
            'minimal_versi_lms' => 'nullable|string|max:30',
            'checksum_sha256' => 'nullable|string|max:64',
            'file' => 'nullable|file|mimes:zip|max:307200', // max 300MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->storeAs('releases', 'aksaraedu-lms-'.$validated['nomor_versi'].'.zip');
            if (empty($validated['checksum_sha256'])) {
                $validated['checksum_sha256'] = hash_file('sha256', storage_path('app/'.$filePath));
            }
        }

        $checksum = $validated['checksum_sha256'] ?? hash('sha256', $validated['nomor_versi'].now());

        // Digital signature RSA-4096 untuk integritas rilis
        $fileSignature = $this->licenseSigner->signPayload([
            'nomor_versi' => $validated['nomor_versi'],
            'checksum_sha256' => $checksum,
            'published_at' => now()->toIso8601String(),
        ]);

        $release = RilisPembaruan::updateOrCreate(
            ['nomor_versi' => $validated['nomor_versi']],
            [
                'tipe_rilis' => $validated['tipe_rilis'] ?? 'patch_bugfix',
                'ringkasan_perubahan' => $validated['ringkasan_perubahan'] ?? "Rilis otomatis versi {$validated['nomor_versi']} via GitHub Actions CI/CD pipeline.",
                'minimal_versi_lms' => $validated['minimal_versi_lms'] ?? '1.0.0',
                'is_public' => true,
                'is_critical_patch' => false,
                'file_path_zip' => $filePath,
                'checksum_sha256' => $checksum,
                'file_signature' => $fileSignature,
                'published_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => "Paket rilis v{$release->nomor_versi} berhasil dipublikasikan ke Registry Central Hub.",
            'data' => [
                'id' => $release->id,
                'nomor_versi' => $release->nomor_versi,
                'tipe_rilis' => $release->tipe_rilis,
                'checksum_sha256' => $release->checksum_sha256,
                'file_path_zip' => $release->file_path_zip,
                'published_at' => $release->published_at?->toIso8601String(),
            ],
        ], 201);
    }
}

