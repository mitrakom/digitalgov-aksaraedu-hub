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

class KlienController extends Controller
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {}

    public function index(Request $request): Response
    {
        $query = KlienSekolah::with(['lisensis' => function ($q) {
            $q->latest();
        }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_sekolah', 'like', "%{$search}%")
                    ->orWhere('npsn', 'like', "%{$search}%")
                    ->orWhere('provinsi', 'like', "%{$search}%")
                    ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                    ->orWhere('nama_pic', 'like', "%{$search}%");
            });
        }

        if ($tipe = $request->input('tipe_sekolah')) {
            $query->where('tipe_sekolah', $tipe);
        }

        if ($status = $request->input('status_klien')) {
            $query->where('status_klien', $status);
        }

        $kliens = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/klien/Index', [
            'kliens' => $kliens,
            'filters' => $request->only(['search', 'tipe_sekolah', 'status_klien']),
            'publicKey' => $this->licenseSigner->getPublicKey(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'npsn' => 'required|string|size:8|unique:klien_sekolahs,npsn',
            'nama_sekolah' => 'required|string|max:150',
            'tipe_sekolah' => 'required|in:sma,smk,ma,mak,smp,mts',
            'yayasan_induk' => 'nullable|string|max:150',
            'nama_pic' => 'required|string|max:100',
            'kontak_pic_wa' => 'required|string|max:25',
            'email_pic' => 'required|email|max:100',
            'provinsi' => 'required|string|max:100',
            'kabupaten_kota' => 'required|string|max:100',
            'alamat_lengkap' => 'nullable|string',
            'status_klien' => 'required|in:aktif,prospek,berhenti',
            'buat_lisensi' => 'nullable|boolean',
            'model_lisensi' => 'nullable|in:beli_putus,langganan',
            'tier_paket' => 'nullable|in:lite,standar,enterprise',
            'domain_terdaftar' => 'nullable|string|max:100',
            'durasi_bulan' => 'nullable|integer|min:1',
            'garansi_bulan' => 'nullable|integer|min:0',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'catatan_kontrak' => 'nullable|string',
        ]);

        $klien = KlienSekolah::create([
            'npsn' => $validated['npsn'],
            'nama_sekolah' => $validated['nama_sekolah'],
            'tipe_sekolah' => $validated['tipe_sekolah'],
            'yayasan_induk' => $validated['yayasan_induk'] ?? null,
            'nama_pic' => $validated['nama_pic'],
            'kontak_pic_wa' => $validated['kontak_pic_wa'],
            'email_pic' => $validated['email_pic'],
            'provinsi' => $validated['provinsi'],
            'kabupaten_kota' => $validated['kabupaten_kota'],
            'alamat_lengkap' => $validated['alamat_lengkap'] ?? null,
            'status_klien' => $validated['status_klien'],
        ]);

        if (!empty($validated['buat_lisensi'])) {
            $this->issueLicenseForKlien($klien, [
                'model_lisensi' => $validated['model_lisensi'] ?? 'beli_putus',
                'tier_paket' => $validated['tier_paket'] ?? 'enterprise',
                'domain_terdaftar' => $validated['domain_terdaftar'] ?? null,
                'durasi_bulan' => $validated['durasi_bulan'] ?? 12,
                'garansi_bulan' => $validated['garansi_bulan'] ?? 3,
                'nilai_kontrak' => $validated['nilai_kontrak'] ?? 0,
                'catatan_kontrak' => $validated['catatan_kontrak'] ?? 'Penerbitan otomatis saat registrasi sekolah.',
            ]);

            return redirect()->route('admin.klien.index')
                ->with('success', "Data sekolah {$klien->nama_sekolah} dan lisensi resmi berhasil dibuat.");
        }

        return redirect()->route('admin.klien.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function storeLisensi(Request $request, string $id): RedirectResponse
    {
        $klien = KlienSekolah::findOrFail($id);

        $validated = $request->validate([
            'model_lisensi' => 'required|in:beli_putus,langganan',
            'tier_paket' => 'required|in:lite,standar,enterprise',
            'domain_terdaftar' => 'nullable|string|max:100',
            'durasi_bulan' => 'nullable|integer|min:1',
            'garansi_bulan' => 'nullable|integer|min:0',
            'nilai_kontrak' => 'required|numeric|min:0',
            'catatan_kontrak' => 'nullable|string',
        ]);

        $this->issueLicenseForKlien($klien, $validated);

        return redirect()->back()->with('success', "Lisensi resmi untuk {$klien->nama_sekolah} berhasil diterbitkan.");
    }

    protected function issueLicenseForKlien(KlienSekolah $klien, array $data): Lisensi
    {
        $tanggalRilis = now();
        $year = $tanggalRilis->format('Y');
        $tipe = strtoupper($klien->tipe_sekolah);
        $nomorLisensi = "LIC-{$year}-{$tipe}-{$klien->npsn}";

        $tanggalKadaluarsa = null;
        if (($data['model_lisensi'] ?? 'beli_putus') === 'langganan') {
            $durasiBulan = !empty($data['durasi_bulan']) ? (int) $data['durasi_bulan'] : 12;
            $tanggalKadaluarsa = $tanggalRilis->copy()->addMonths($durasiBulan);
            $garansiBugfix = $tanggalKadaluarsa->copy();
        } else {
            $garansiBulan = isset($data['garansi_bulan']) ? (int) $data['garansi_bulan'] : 3;
            $garansiBugfix = $tanggalRilis->copy()->addMonths($garansiBulan);
        }

        $serialKey = $this->licenseSigner->generateSerialKey($klien->tipe_sekolah);
        $tokenApi = $this->licenseSigner->generateApiToken();

        $lisensi = Lisensi::create([
            'klien_sekolah_id' => $klien->id,
            'nomor_lisensi' => $nomorLisensi,
            'serial_key' => $serialKey,
            'model_lisensi' => $data['model_lisensi'] ?? 'beli_putus',
            'tier_paket' => $data['tier_paket'] ?? 'enterprise',
            'token_api' => $tokenApi,
            'domain_terdaftar' => $data['domain_terdaftar'] ?: null,
            'tanggal_rilis' => $tanggalRilis,
            'tanggal_kadaluarsa' => $tanggalKadaluarsa,
            'garansi_bugfix_hingga' => $garansiBugfix,
            'status' => 'active',
            'nilai_kontrak' => $data['nilai_kontrak'] ?? 0,
            'catatan_kontrak' => $data['catatan_kontrak'] ?? null,
            'allowed_features' => [
                'cbt_engine',
                'kurikulum_merdeka',
                'multimedia_materials',
                'leger_nilai',
                'presensi_qr',
                'rapor_otomatis',
            ],
        ]);

        $signedPayload = $this->licenseSigner->generateSignedLicensePayload($lisensi);
        $lisensi->update(['signed_license_payload' => $signedPayload]);

        return $lisensi;
    }

    public function show(string $id): Response
    {
        $klien = KlienSekolah::with(['lisensis' => function ($q) {
            $q->latest();
        }, 'lisensis.telemetriHeartbeats' => function ($q) {
            $q->latest('waktu_ping')->take(5);
        }, 'tiketDukungans' => function ($q) {
            $q->latest();
        }])->findOrFail($id);

        return Inertia::render('admin/klien/Show', [
            'klien' => $klien,
            'publicKey' => $this->licenseSigner->getPublicKey(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $klien = KlienSekolah::findOrFail($id);

        $validated = $request->validate([
            'npsn' => "required|string|size:8|unique:klien_sekolahs,npsn,{$klien->id}",
            'nama_sekolah' => 'required|string|max:150',
            'tipe_sekolah' => 'required|in:sma,smk,ma,mak,smp,mts',
            'yayasan_induk' => 'nullable|string|max:150',
            'nama_pic' => 'required|string|max:100',
            'kontak_pic_wa' => 'required|string|max:25',
            'email_pic' => 'required|email|max:100',
            'provinsi' => 'required|string|max:100',
            'kabupaten_kota' => 'required|string|max:100',
            'alamat_lengkap' => 'nullable|string',
            'status_klien' => 'required|in:aktif,prospek,berhenti',
        ]);

        $klien->update($validated);

        return redirect()->back()->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $klien = KlienSekolah::findOrFail($id);
        $klien->delete();

        return redirect()->route('admin.klien.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }
}

