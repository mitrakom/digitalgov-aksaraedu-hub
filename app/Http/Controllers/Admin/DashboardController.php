<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlienSekolah;
use App\Models\LeadsDemo;
use App\Models\Lisensi;
use App\Models\RilisPembaruan;
use App\Models\TelemetriHeartbeat;
use App\Models\TiketDukungan;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalKlien = KlienSekolah::count();
        $totalBeliPutus = Lisensi::where('model_lisensi', 'beli_putus')->count();
        $totalLangganan = Lisensi::where('model_lisensi', 'langganan')->count();
        $totalRevenue = Lisensi::sum('nilai_kontrak');

        // Peringatan lisensi mendekati jatuh tempo (< 30 hari)
        $expiringLicenses = Lisensi::with('klienSekolah')
            ->where('model_lisensi', 'langganan')
            ->whereNotNull('tanggal_kadaluarsa')
            ->where('tanggal_kadaluarsa', '<=', now()->addDays(30))
            ->where('status', '!=', 'revoked')
            ->orderBy('tanggal_kadaluarsa', 'asc')
            ->get();

        // Klien dengan garansi bugfix aktif
        $warrantyCount = Lisensi::where('model_lisensi', 'beli_putus')
            ->whereNotNull('garansi_bugfix_hingga')
            ->where('garansi_bugfix_hingga', '>=', now())
            ->count();

        // Telemetri realtime 10 rekaman terbaru
        $recentTelemetry = TelemetriHeartbeat::with('lisensi.klienSekolah')
            ->latest('waktu_ping')
            ->take(8)
            ->get();

        // Tiket butuh penanganan
        $pendingTickets = TiketDukungan::with('klienSekolah')
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->take(5)
            ->get();

        // Leads baru
        $recentLeads = LeadsDemo::where('status_followup', 'baru')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_klien' => $totalKlien,
                'total_beli_putus' => $totalBeliPutus,
                'total_langganan' => $totalLangganan,
                'total_revenue' => $totalRevenue,
                'total_leads_baru' => LeadsDemo::where('status_followup', 'baru')->count(),
                'total_tiket_open' => TiketDukungan::where('status', 'open')->count(),
                'active_warranty_count' => $warrantyCount,
            ],
            'expiringLicenses' => $expiringLicenses,
            'recentTelemetry' => $recentTelemetry,
            'pendingTickets' => $pendingTickets,
            'recentLeads' => $recentLeads,
        ]);
    }
}
