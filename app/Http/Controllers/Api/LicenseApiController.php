<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KlienSekolah;
use App\Models\Lisensi;
use App\Models\Pengumuman;
use App\Models\RilisPembaruan;
use App\Models\TelemetriHeartbeat;
use App\Services\LicenseSignerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class LicenseApiController extends Controller
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {
    }

    /**
     * Aktivasi Lisensi Awal (POST /api/v1/license/activate)
     */
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'npsn' => 'required|string',
            'token_aktivasi' => 'required|string',
            'domain_host' => 'nullable|string',
            'hardware_uuid' => 'nullable|string',
            'versi_lms_terpasang' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi request gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $npsn = trim($request->input('npsn'));
        $token = trim($request->input('token_aktivasi'));

        $klien = KlienSekolah::where('npsn', $npsn)->first();
        if (! $klien) {
            return response()->json([
                'status' => 'error',
                'message' => 'NPSN sekolah tidak terdaftar dalam sistem AksaraEdu.',
            ], 404);
        }

        // Cari lisensi berdasarkan serial_key atau token_api atau nomor_lisensi
        $lisensi = Lisensi::where('klien_sekolah_id', $klien->id)
            ->where(function ($q) use ($token) {
                $q->where('serial_key', $token)
                    ->orWhere('token_api', $token)
                    ->orWhere('nomor_lisensi', $token);
            })
            ->first();

        if (! $lisensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token aktivasi / Serial Key tidak cocok dengan data NPSN sekolah.',
            ], 403);
        }

        if ($lisensi->status === 'revoked') {
            return response()->json([
                'status' => 'error',
                'message' => 'Lisensi ini telah dicabut (Revoked) oleh AksaraEdu Central Hub.',
            ], 403);
        }

        // Bind domain host & hardware UUID jika belum terikat
        if ($request->filled('domain_host') && empty($lisensi->domain_terdaftar)) {
            $lisensi->domain_terdaftar = $request->input('domain_host');
        }

        if ($request->filled('hardware_uuid') && empty($lisensi->hardware_fingerprint)) {
            $lisensi->hardware_fingerprint = $request->input('hardware_uuid');
        }

        // Generate signed license payload
        $signedPayload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
        $lisensi->signed_license_payload = $signedPayload;
        $lisensi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Lisensi berhasil diaktivasi.',
            'data' => [
                'nomor_lisensi' => $lisensi->nomor_lisensi,
                'serial_key' => $lisensi->serial_key,
                'model_lisensi' => $lisensi->model_lisensi,
                'tier_paket' => $lisensi->tier_paket,
                'token_api' => $lisensi->token_api,
                'signed_license_key' => $signedPayload,
                'garansi_bugfix_hingga' => $lisensi->garansi_bugfix_hingga?->toDateString(),
                'tanggal_kadaluarsa' => $lisensi->tanggal_kadaluarsa?->toDateString(),
                'public_key' => $this->licenseSigner->getPublicKey(),
            ],
        ]);
    }

    /**
     * Telemetri & Heartbeat berkala (POST /api/v1/license/heartbeat)
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?: $request->header('X-License-Token') ?: $request->input('token_api');

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization token lisensi tidak ditemukan.',
            ], 401);
        }

        $lisensi = Lisensi::with('klienSekolah')->where('token_api', $token)->first();
        if (! $lisensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token lisensi tidak valid.',
            ], 403);
        }

        // Record Telemetry
        $metrik = $request->input('metrik', []);
        TelemetriHeartbeat::create([
            'lisensi_id' => $lisensi->id,
            'ip_address' => $request->ip(),
            'domain_terdeteksi' => $request->input('domain') ?: $request->getHost(),
            'versi_lms' => $request->input('versi_lms', '1.0.0'),
            'versi_php' => $request->input('versi_php'),
            'total_siswa_aktif' => (int) ($metrik['total_siswa'] ?? 0),
            'total_guru_aktif' => (int) ($metrik['total_guru'] ?? 0),
            'total_rombel_aktif' => (int) ($metrik['total_rombel'] ?? 0),
            'total_ujian_cbt' => (int) ($metrik['total_ujian_cbt'] ?? 0),
            'db_size_mb' => isset($metrik['db_size_mb']) ? (float) $metrik['db_size_mb'] : null,
            'waktu_ping' => now(),
        ]);

        if ($lisensi->status === 'revoked') {
            return response()->json([
                'status' => 'revoked',
                'message' => 'Lisensi telah dicabut. Hubungi AksaraEdu Support.',
            ], 403);
        }

        // Periksa status masa langganan (untuk model SaaS / Langganan)
        $gracePeriodDays = null;
        if ($lisensi->model_lisensi === 'langganan' && $lisensi->tanggal_kadaluarsa) {
            if (now()->gt($lisensi->tanggal_kadaluarsa)) {
                $daysExpired = now()->diffInDays($lisensi->tanggal_kadaluarsa);
                if ($daysExpired <= 14) {
                    $gracePeriodDays = 14 - $daysExpired;
                    $lisensi->update(['status' => 'grace_period']);

                    return response()->json([
                        'status' => 'grace_period',
                        'grace_period_remaining_days' => $gracePeriodDays,
                        'message' => "Masa langganan telah berakhir. Sisa waktu masa tenggang: {$gracePeriodDays} hari.",
                    ], 402);
                } else {
                    $lisensi->update(['status' => 'expired']);

                    return response()->json([
                        'status' => 'expired',
                        'message' => 'Masa langganan dan grace period telah habis. Akses dibatasi mode read-only.',
                    ], 402);
                }
            }
        }

        // Cek update rilis terbaru
        $latestRelease = RilisPembaruan::where('is_public', true)->latest('published_at')->first();
        $hasPendingUpdate = false;
        if ($latestRelease && version_compare($latestRelease->nomor_versi, $request->input('versi_lms', '1.0.0'), '>')) {
            $hasPendingUpdate = true;
        }

        // Cek pengumuman broadcast aktif
        $announcement = Pengumuman::active()
            ->where(function ($q) use ($lisensi) {
                $q->where('target_model', 'semua')
                    ->orWhere('target_model', $lisensi->model_lisensi);
            })
            ->latest()
            ->first();

        return response()->json([
            'status' => 'active',
            'grace_period_remaining_days' => null,
            'has_pending_update' => $hasPendingUpdate,
            'latest_version' => $latestRelease?->nomor_versi,
            'announcement' => $announcement ? [
                'id' => $announcement->id,
                'judul' => $announcement->judul,
                'pesan' => $announcement->pesan,
                'tipe' => $announcement->tipe,
            ] : null,
        ]);
    }

    /**
     * Portal Verifikasi Lisensi NPSN Publik (GET /api/v1/license/verify/{npsn})
     */
    public function verifyNpsn(string $npsn): JsonResponse
    {
        $klien = KlienSekolah::with('lisensis')->where('npsn', trim($npsn))->first();

        if (! $klien) {
            return response()->json([
                'verified' => false,
                'message' => 'NPSN belum terdaftar pada basis data resmi AksaraEdu.',
            ], 404);
        }

        $activeLicense = $klien->lisensis()->whereIn('status', ['active', 'grace_period'])->latest()->first();

        if (! $activeLicense) {
            return response()->json([
                'verified' => false,
                'nama_sekolah' => $klien->nama_sekolah,
                'tipe_sekolah' => strtoupper($klien->tipe_sekolah),
                'provinsi' => $klien->provinsi,
                'kabupaten_kota' => $klien->kabupaten_kota,
                'message' => 'Sekolah terdaftar namun lisensi saat ini tidak aktif.',
            ]);
        }

        return response()->json([
            'verified' => true,
            'npsn' => $klien->npsn,
            'nama_sekolah' => $klien->nama_sekolah,
            'tipe_sekolah' => strtoupper($klien->tipe_sekolah),
            'provinsi' => $klien->provinsi,
            'kabupaten_kota' => $klien->kabupaten_kota,
            'model_lisensi' => $activeLicense->model_lisensi === 'beli_putus' ? 'Beli Putus On-Premise' : 'Langganan SaaS',
            'tier_paket' => ucfirst($activeLicense->tier_paket),
            'status' => $activeLicense->status,
            'garansi_aktif' => $activeLicense->isWarrantyActive(),
            'garansi_hingga' => $activeLicense->garansi_bugfix_hingga?->translatedFormat('d F Y'),
            'tahun_terbit' => $activeLicense->tanggal_rilis?->format('Y'),
        ]);
    }
}
