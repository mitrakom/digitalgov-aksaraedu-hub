<?php

namespace App\Http\Controllers;

use App\Models\KlienSekolah;
use App\Models\RilisPembaruan;
use App\Models\TelemetriHeartbeat;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    /**
     * Public Landing Page
     */
    public function index(): Response
    {
        $stats = [
            'total_sekolah' => KlienSekolah::where('status_klien', 'aktif')->count(),
            'total_siswa_terlayani' => TelemetriHeartbeat::latest('waktu_ping')->sum('total_siswa_aktif') ?: 1820,
            'provinsi_tercakup' => KlienSekolah::distinct('provinsi')->count('provinsi') ?: 3,
            'latest_version' => RilisPembaruan::where('is_public', true)->latest('published_at')->value('nomor_versi') ?: '1.0.1',
        ];

        return Inertia::render('public/Landing', [
            'stats' => $stats,
        ]);
    }

    /**
     * Interactive Pricing Calculator & Quotation
     */
    public function pricing(): Response
    {
        return Inertia::render('public/PricingCalculator');
    }

    /**
     * Portal Verifikasi Lisensi NPSN Resmi
     */
    public function verify(): Response
    {
        return Inertia::render('public/VerifyLicense');
    }

    /**
     * Halaman Coba Demo 1-Klik
     */
    public function demo(): Response
    {
        return Inertia::render('public/DemoRequest');
    }
}
