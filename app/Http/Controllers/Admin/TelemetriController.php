<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelemetriHeartbeat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TelemetriController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TelemetriHeartbeat::with('lisensi.klienSekolah');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('domain_terdeteksi', 'like', "%{$search}%")
                    ->orWhere('versi_lms', 'like', "%{$search}%")
                    ->orWhereHas('lisensi.klienSekolah', function ($sq) use ($search) {
                        $sq->where('nama_sekolah', 'like', "%{$search}%")
                            ->orWhere('npsn', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->latest('waktu_ping')->paginate(15)->withQueryString();

        // Agregat metrik terkini
        $totalSiswa = TelemetriHeartbeat::latest('waktu_ping')->sum('total_siswa_aktif');
        $totalGuru = TelemetriHeartbeat::latest('waktu_ping')->sum('total_guru_aktif');
        $totalUjian = TelemetriHeartbeat::latest('waktu_ping')->sum('total_ujian_cbt');

        return Inertia::render('admin/telemetri/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search']),
            'summary' => [
                'total_siswa' => $totalSiswa,
                'total_guru' => $totalGuru,
                'total_ujian' => $totalUjian,
            ],
        ]);
    }
}
