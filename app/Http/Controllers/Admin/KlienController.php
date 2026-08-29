<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlienSekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KlienController extends Controller
{
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
        ]);

        KlienSekolah::create($validated);

        return redirect()->route('admin.klien.index')
            ->with('success', 'Data sekolah mitra berhasil ditambahkan.');
    }

    public function show(string $id): Response
    {
        $klien = KlienSekolah::with(['lisensis.telemetriHeartbeats' => function ($q) {
            $q->latest('waktu_ping')->take(5);
        }, 'tiketDukungans' => function ($q) {
            $q->latest();
        }])->findOrFail($id);

        return Inertia::render('admin/klien/Show', [
            'klien' => $klien,
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

        return redirect()->back()->with('success', 'Data sekolah mitra berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $klien = KlienSekolah::findOrFail($id);
        $klien->delete();

        return redirect()->route('admin.klien.index')
            ->with('success', 'Data sekolah mitra berhasil dihapus.');
    }
}
