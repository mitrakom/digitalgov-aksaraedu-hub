<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlienSekolah;
use App\Models\TiketDukungan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TiketController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TiketDukungan::with('klienSekolah');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($prioritas = $request->input('prioritas')) {
            $query->where('prioritas', $prioritas);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tiket', 'like', "%{$search}%")
                  ->orWhere('judul_masalah', 'like', "%{$search}%")
                  ->orWhereHas('klienSekolah', function ($sq) use ($search) {
                      $sq->where('nama_sekolah', 'like', "%{$search}%");
                  });
            });
        }

        $tikets = $query->latest()->paginate(10)->withQueryString();
        $kliens = KlienSekolah::orderBy('nama_sekolah')->select('id', 'nama_sekolah', 'npsn')->get();

        return Inertia::render('admin/tiket/Index', [
            'tikets' => $tikets,
            'kliens' => $kliens,
            'filters' => $request->only(['status', 'prioritas', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'klien_sekolah_id' => 'required|exists:klien_sekolahs,id',
            'judul_masalah' => 'required|string|max:200',
            'deskripsi_kendala' => 'required|string',
            'kategori' => 'required|in:bug_sistem,pertanyaan_fitur,instalasi,darurat',
            'prioritas' => 'required|in:rendah,sedang,tinggi,kritis',
            'is_garansi_claim' => 'boolean',
        ]);

        $klien = KlienSekolah::with('lisensis')->findOrFail($validated['klien_sekolah_id']);
        $activeLicense = $klien->active_lisensi;

        $isGaransi = $request->boolean('is_garansi_claim') || ($activeLicense && $activeLicense->isWarrantyActive());
        $slaDeadline = $isGaransi ? now()->addHours(24) : now()->addHours(72);

        $count = TiketDukungan::count() + 1;
        $nomorTiket = 'TKT-' . date('Y') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        TiketDukungan::create([
            'klien_sekolah_id' => $klien->id,
            'nomor_tiket' => $nomorTiket,
            'judul_masalah' => $validated['judul_masalah'],
            'deskripsi_kendala' => $validated['deskripsi_kendala'],
            'kategori' => $validated['kategori'],
            'prioritas' => $validated['prioritas'],
            'status' => 'open',
            'is_garansi_claim' => $isGaransi,
            'sla_deadline' => $slaDeadline,
        ]);

        return redirect()->route('admin.tiket.index')
            ->with('success', "Tiket Bantuan {$nomorTiket} berhasil dibuka.");
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $tiket = TiketDukungan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'tanggapan_admin' => 'nullable|string',
        ]);

        $tiket->status = $validated['status'];
        if (isset($validated['tanggapan_admin'])) {
            $tiket->tanggapan_admin = $validated['tanggapan_admin'];
        }

        if ($validated['status'] === 'resolved' || $validated['status'] === 'closed') {
            $tiket->resolved_at = now();
        }

        $tiket->save();

        return redirect()->back()->with('success', 'Status tiket dukungan berhasil diperbarui.');
    }
}
