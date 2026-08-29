<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadsDemo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = LeadsDemo::query();

        if ($status = $request->input('status_followup')) {
            $query->where('status_followup', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('nama_sekolah', 'like', "%{$search}%")
                    ->orWhere('nomor_wa', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['status_followup', 'search']),
        ]);
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $lead = LeadsDemo::findOrFail($id);

        $validated = $request->validate([
            'status_followup' => 'required|in:baru,dihubungi,presentasi,deal,lost',
            'catatan_sales' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->back()->with('success', 'Status follow-up lead berhasil diperbarui.');
    }
}
