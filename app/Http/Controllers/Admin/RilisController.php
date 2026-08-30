<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RilisPembaruan;
use App\Models\RiwayatUpdate;
use App\Services\LicenseSignerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RilisController extends Controller
{
    public function __construct(
        protected LicenseSignerService $licenseSigner
    ) {}

    public function index(): Response
    {
        $releases = RilisPembaruan::withCount('riwayatUpdates')
            ->latest('published_at')
            ->paginate(10);

        $recentDownloads = RiwayatUpdate::with(['rilisPembaruan', 'lisensi.klienSekolah'])
            ->latest('downloaded_at')
            ->take(15)
            ->get();

        return Inertia::render('admin/rilis/Index', [
            'releases' => $releases,
            'recentDownloads' => $recentDownloads,
        ]);
    }

    public function download(string $id): BinaryFileResponse|RedirectResponse
    {
        $release = RilisPembaruan::findOrFail($id);

        if ($release->file_path_zip) {
            $possiblePaths = [
                Storage::disk('local')->path($release->file_path_zip),
                storage_path('app/private/'.$release->file_path_zip),
                storage_path('app/'.$release->file_path_zip),
                storage_path('app/public/'.$release->file_path_zip),
            ];

            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath) && is_file($fullPath)) {
                    return response()->download($fullPath, "aksaraedu-lms-{$release->nomor_versi}-release.zip");
                }
            }
        }

        return back()->with('error', 'Berkas .zip fisik belum diunggah atau tidak ditemukan di storage server.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_versi' => 'required|string|max:30|unique:rilis_pembaruans,nomor_versi',
            'tipe_rilis' => 'required|in:patch_bugfix,minor_feature,major_curriculum',
            'ringkasan_perubahan' => 'required|string',
            'minimal_versi_lms' => 'required|string|max:30',
            'is_public' => 'boolean',
            'is_critical_patch' => 'boolean',
            'checksum_sha256' => 'nullable|string|max:64',
        ]);

        $checksum = $validated['checksum_sha256'] ?: hash('sha256', $validated['nomor_versi'].now());

        // Digital signature for release integrity
        $fileSignature = $this->licenseSigner->signPayload([
            'nomor_versi' => $validated['nomor_versi'],
            'checksum_sha256' => $checksum,
            'published_at' => now()->toIso8601String(),
        ]);

        RilisPembaruan::create([
            'nomor_versi' => $validated['nomor_versi'],
            'tipe_rilis' => $validated['tipe_rilis'],
            'ringkasan_perubahan' => $validated['ringkasan_perubahan'],
            'minimal_versi_lms' => $validated['minimal_versi_lms'],
            'is_public' => $request->boolean('is_public', true),
            'is_critical_patch' => $request->boolean('is_critical_patch', false),
            'checksum_sha256' => $checksum,
            'file_signature' => $fileSignature,
            'published_at' => now(),
        ]);

        return redirect()->route('admin.rilis.index')
            ->with('success', "Paket Rilis v{$validated['nomor_versi']} berhasil dipublikasikan ke Registry Pusat.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $release = RilisPembaruan::findOrFail($id);
        $release->delete();

        return redirect()->route('admin.rilis.index')
            ->with('success', 'Rilis pembaruan berhasil dihapus.');
    }
}
