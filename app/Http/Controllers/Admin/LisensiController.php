<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlienSekolah;
use App\Models\Lisensi;
use App\Services\LicenseSignerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LisensiController extends Controller
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {}

    public function index(Request $request): Response
    {
        $query = Lisensi::with('klienSekolah');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lisensi', 'like', "%{$search}%")
                    ->orWhere('serial_key', 'like', "%{$search}%")
                    ->orWhere('domain_terdaftar', 'like', "%{$search}%")
                    ->orWhereHas('klienSekolah', function ($sq) use ($search) {
                        $sq->where('nama_sekolah', 'like', "%{$search}%")
                            ->orWhere('npsn', 'like', "%{$search}%");
                    });
            });
        }

        if ($model = $request->input('model_lisensi')) {
            $query->where('model_lisensi', $model);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $lisensis = $query->latest()->paginate(10)->withQueryString();
        $kliens = KlienSekolah::orderBy('nama_sekolah')->select('id', 'nama_sekolah', 'npsn', 'tipe_sekolah')->get();

        return Inertia::render('admin/lisensi/Index', [
            'lisensis' => $lisensis,
            'kliens' => $kliens,
            'filters' => $request->only(['search', 'model_lisensi', 'status']),
            'publicKey' => $this->licenseSigner->getPublicKey(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'klien_sekolah_id' => 'required|exists:klien_sekolahs,id',
            'model_lisensi' => 'required|in:beli_putus,langganan',
            'tier_paket' => 'required|in:lite,standar,enterprise',
            'domain_terdaftar' => 'nullable|string|max:100',
            'tanggal_rilis' => 'required|date',
            'durasi_bulan' => 'nullable|integer|min:1',
            'garansi_bulan' => 'nullable|integer|min:0',
            'nilai_kontrak' => 'required|numeric|min:0',
            'catatan_kontrak' => 'nullable|string',
        ]);

        $klien = KlienSekolah::findOrFail($validated['klien_sekolah_id']);
        $tanggalRilis = Carbon::parse($validated['tanggal_rilis']);

        // Generate Nomor Lisensi format: LIC-YYYY-{TIPE}-{NPSN}
        $year = $tanggalRilis->format('Y');
        $tipe = strtoupper($klien->tipe_sekolah);
        $nomorLisensi = "LIC-{$year}-{$tipe}-{$klien->npsn}";

        // Tanggal Kadaluarsa & Garansi
        $tanggalKadaluarsa = null;
        if ($validated['model_lisensi'] === 'langganan') {
            $durasiBulan = $validated['durasi_bulan'] ?: 12;
            $tanggalKadaluarsa = $tanggalRilis->copy()->addMonths($durasiBulan);
            $garansiBugfix = $tanggalKadaluarsa->copy();
        } else {
            // Beli Putus default 3 Bulan Garansi Bugfix Resmi
            $garansiBulan = isset($validated['garansi_bulan']) ? (int) $validated['garansi_bulan'] : 3;
            $garansiBugfix = $tanggalRilis->copy()->addMonths($garansiBulan);
        }

        $serialKey = $this->licenseSigner->generateSerialKey($klien->tipe_sekolah);
        $tokenApi = $this->licenseSigner->generateApiToken();

        $lisensi = Lisensi::create([
            'klien_sekolah_id' => $klien->id,
            'nomor_lisensi' => $nomorLisensi,
            'serial_key' => $serialKey,
            'model_lisensi' => $validated['model_lisensi'],
            'tier_paket' => $validated['tier_paket'],
            'token_api' => $tokenApi,
            'domain_terdaftar' => $validated['domain_terdaftar'] ?: null,
            'tanggal_rilis' => $tanggalRilis,
            'tanggal_kadaluarsa' => $tanggalKadaluarsa,
            'garansi_bugfix_hingga' => $garansiBugfix,
            'status' => 'active',
            'nilai_kontrak' => $validated['nilai_kontrak'],
            'catatan_kontrak' => $validated['catatan_kontrak'],
            'allowed_features' => [
                'cbt_engine',
                'kurikulum_merdeka',
                'multimedia_materials',
                'leger_nilai',
                'presensi_qr',
                'rapor_otomatis',
            ],
        ]);

        // Generate signed license payload
        $signedPayload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
        $lisensi->update(['signed_license_payload' => $signedPayload]);

        return redirect()->route('admin.lisensi.index')
            ->with('success', "Lisensi resmi untuk {$klien->nama_sekolah} berhasil diterbitkan dengan nomor {$nomorLisensi}.");
    }

    public function renew(Request $request, string $id): RedirectResponse
    {
        $lisensi = Lisensi::with('klienSekolah')->findOrFail($id);

        $validated = $request->validate([
            'perpanjang_bulan' => 'required|integer|min:1',
            'nilai_kontrak_tambahan' => 'nullable|numeric|min:0',
        ]);

        $baseDate = ($lisensi->tanggal_kadaluarsa && $lisensi->tanggal_kadaluarsa->isFuture())
            ? $lisensi->tanggal_kadaluarsa
            : now();

        $newExpiry = $baseDate->copy()->addMonths((int) $validated['perpanjang_bulan']);
        $lisensi->tanggal_kadaluarsa = $newExpiry;
        $lisensi->garansi_bugfix_hingga = $newExpiry;
        $lisensi->status = 'active';

        if (! empty($validated['nilai_kontrak_tambahan'])) {
            $lisensi->nilai_kontrak += (float) $validated['nilai_kontrak_tambahan'];
        }

        $lisensi->signed_license_payload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
        $lisensi->save();

        return redirect()->back()->with('success', 'Masa aktif lisensi berhasil diperpanjang hingga '.$newExpiry->format('d M Y'));
    }

    public function resetHardware(string $id): RedirectResponse
    {
        $lisensi = Lisensi::findOrFail($id);
        $lisensi->hardware_fingerprint = null;
        $lisensi->hardware_reset_count += 1;
        $lisensi->signed_license_payload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
        $lisensi->save();

        return redirect()->back()->with('success', 'Kaitan Hardware Fingerprint berhasil di-reset. Server sekolah dapat melakukan binding ulang.');
    }

    public function revoke(string $id): RedirectResponse
    {
        $lisensi = Lisensi::findOrFail($id);
        $lisensi->status = 'revoked';
        $lisensi->save();

        return redirect()->back()->with('success', 'Lisensi berhasil dicabut (Revoked). Klien tidak dapat lagi melakukan validasi/sinkronisasi.');
    }

    public function downloadLicenseFile(string $id): HttpResponse
    {
        $lisensi = Lisensi::with('klienSekolah')->findOrFail($id);

        if (empty($lisensi->signed_license_payload)) {
            $lisensi->signed_license_payload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
            $lisensi->save();
        }

        $content = json_encode([
            'aksaraedu_license_file' => 'v1.0',
            'nomor_lisensi' => $lisensi->nomor_lisensi,
            'npsn' => $lisensi->klienSekolah->npsn,
            'nama_sekolah' => $lisensi->klienSekolah->nama_sekolah,
            'model_lisensi' => $lisensi->model_lisensi,
            'tier_paket' => $lisensi->tier_paket,
            'signed_package' => $lisensi->signed_license_payload,
            'public_key' => $this->licenseSigner->getPublicKey(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $filename = "aksaraedu-{$lisensi->klienSekolah->npsn}.lic";

        return response($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
