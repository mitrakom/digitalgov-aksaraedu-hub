<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengumumanController extends Controller
{
    public function index(): Response
    {
        $pengumumans = Pengumuman::latest()->paginate(10);

        return Inertia::render('admin/pengumuman/Index', [
            'pengumumans' => $pengumumans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',
            'pesan' => 'required|string',
            'tipe' => 'required|in:info,warning,urgent',
            'target_model' => 'required|in:semua,beli_putus,langganan',
            'is_active' => 'boolean',
            'mulai_berlaku' => 'nullable|date',
            'selesai_berlaku' => 'nullable|date',
        ]);

        Pengumuman::create($validated);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman siaran remote berhasil dipublikasikan ke klien LMS.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->is_active = ! $pengumuman->is_active;
        $pengumuman->save();

        return redirect()->back()->with('success', 'Status pengumuman berhasil diubah.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
