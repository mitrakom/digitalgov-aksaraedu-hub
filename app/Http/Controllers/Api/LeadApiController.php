<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadsDemo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LeadApiController extends Controller
{
    /**
     * Submit Demo Request / Lead (POST /api/v1/leads/demo)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pemohon' => 'required|string|max:100',
            'nama_sekolah' => 'required|string|max:150',
            'tipe_sekolah' => 'required|in:sma,smk,ma,mak,smp,mts',
            'nomor_wa' => 'required|string|max:25',
            'email' => 'required|email|max:100',
            'estimasi_siswa' => 'nullable|integer|min:50|max:10000',
            'model_minat' => 'nullable|in:beli_putus,langganan,belum_tahu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lengkapi form permohonan demo dengan benar.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $demoSlug = Str::slug($request->input('nama_sekolah')).'-'.Str::random(5);
        $demoUrl = "https://demo.aksaraedu.id/{$demoSlug}";

        $lead = LeadsDemo::create([
            'nama_pemohon' => $request->input('nama_pemohon'),
            'nama_sekolah' => $request->input('nama_sekolah'),
            'tipe_sekolah' => $request->input('tipe_sekolah'),
            'nomor_wa' => $request->input('nomor_wa'),
            'email' => $request->input('email'),
            'estimasi_siswa' => $request->input('estimasi_siswa', 500),
            'model_minat' => $request->input('model_minat', 'belum_tahu'),
            'url_demo_terbuat' => $demoUrl,
            'demo_expired_at' => now()->addHours(2),
            'status_followup' => 'baru',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sandbox demo instan berhasil disiapkan (Berlaku 2 Jam).',
            'data' => [
                'id' => $lead->id,
                'demo_url' => $demoUrl,
                'expired_at' => $lead->demo_expired_at->toIso8601String(),
                'username_demo' => 'admin_demo',
                'password_demo' => 'AksaraDemo2026!',
            ],
        ]);
    }
}
