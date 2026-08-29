<?php

namespace Database\Seeders;

use App\Models\KlienSekolah;
use App\Models\LeadsDemo;
use App\Models\Lisensi;
use App\Models\Pengumuman;
use App\Models\RilisPembaruan;
use App\Models\TelemetriHeartbeat;
use App\Models\TiketDukungan;
use App\Models\User;
use App\Services\LicenseSignerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed development environment with comprehensive sample data for testing.
     */
    public function run(): void
    {
        $signer = app(LicenseSignerService::class);
        $signer->ensureKeysExist();

        // 1. Seed Users (Vendor Team RBAC)
        User::firstOrCreate(
            ['email' => 'admin@aksaraedu.id'],
            [
                'name' => 'Admin Eksekutif AksaraEdu',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'phone' => '081234567890',
            ]
        );

        User::firstOrCreate(
            ['email' => 'sales@aksaraedu.id'],
            [
                'name' => 'Budi Santoso (Sales Lead)',
                'password' => Hash::make('password123'),
                'role' => 'sales',
                'phone' => '081298765432',
            ]
        );

        User::firstOrCreate(
            ['email' => 'support@aksaraedu.id'],
            [
                'name' => 'Rian Hidayat (DevOps & Support)',
                'password' => Hash::make('password123'),
                'role' => 'support',
                'phone' => '081345678901',
            ]
        );

        // 2. Klien 1: SMK Negeri 1 Aksara Nusantara (Beli Putus On-Premise)
        $smk1 = KlienSekolah::firstOrCreate(
            ['npsn' => '20104050'],
            [
                'nama_sekolah' => 'SMK Negeri 1 Aksara Nusantara',
                'tipe_sekolah' => 'smk',
                'yayasan_induk' => 'Dinas Pendidikan Provinsi Jawa Barat',
                'nama_pic' => 'Drs. H. Mulyadi, M.Kom',
                'kontak_pic_wa' => '081234567891',
                'email_pic' => 'smkn1aksara@sch.id',
                'provinsi' => 'Jawa Barat',
                'kabupaten_kota' => 'Kota Bandung',
                'alamat_lengkap' => 'Jl. Soekarno Hatta No. 450, Bandung',
                'status_klien' => 'aktif',
            ]
        );

        $lisensiSmk = Lisensi::firstOrCreate(
            ['nomor_lisensi' => 'LIC-2026-SMK-20104050'],
            [
                'klien_sekolah_id' => $smk1->id,
                'serial_key' => 'AKSR-2026-SMK-8821-X992',
                'model_lisensi' => 'beli_putus',
                'tier_paket' => 'enterprise',
                'token_api' => 'aksr_live_smk20104050_98a72b1cd03e',
                'domain_terdaftar' => 'lms.smkn1aksara.sch.id',
                'hardware_fingerprint' => 'NODE-SMK1-Bandung-SRV01-4c4c4544',
                'hardware_reset_count' => 0,
                'tanggal_rilis' => '2026-08-01',
                'tanggal_kadaluarsa' => null,
                'garansi_bugfix_hingga' => '2026-11-01',
                'status' => 'active',
                'nilai_kontrak' => 15000000.00,
                'catatan_kontrak' => 'Lisensi Beli Putus Full Source / On-Premise + 3 Bulan Garansi Bugfix Resmi.',
            ]
        );

        $lisensiSmk->signed_license_payload = $signer->generateSignedLicensePayload($lisensiSmk);
        $lisensiSmk->save();

        // 3. Klien 2: SMA Harapan Bangsa Mandiri (Langganan SaaS)
        $sma1 = KlienSekolah::firstOrCreate(
            ['npsn' => '20205060'],
            [
                'nama_sekolah' => 'SMA Harapan Bangsa Mandiri',
                'tipe_sekolah' => 'sma',
                'yayasan_induk' => 'Yayasan Pendidikan Harapan Bangsa',
                'nama_pic' => 'Siti Rahmawati, S.Pd',
                'kontak_pic_wa' => '081399887766',
                'email_pic' => 'admin@smapertiwi.sch.id',
                'provinsi' => 'DKI Jakarta',
                'kabupaten_kota' => 'Jakarta Selatan',
                'alamat_lengkap' => 'Jl. Fatmawati Raya No. 12, Cilandak',
                'status_klien' => 'aktif',
            ]
        );

        $lisensiSma = Lisensi::firstOrCreate(
            ['nomor_lisensi' => 'LIC-2026-SMA-20205060'],
            [
                'klien_sekolah_id' => $sma1->id,
                'serial_key' => 'AKSR-2026-SMA-3341-M771',
                'model_lisensi' => 'langganan',
                'tier_paket' => 'standar',
                'token_api' => 'aksr_live_sma20205060_11b83c4ef99a',
                'domain_terdaftar' => 'belajar.smapertiwi.sch.id',
                'hardware_fingerprint' => 'CLOUD-VM-SG-JKT01',
                'hardware_reset_count' => 0,
                'tanggal_rilis' => '2026-06-15',
                'tanggal_kadaluarsa' => '2027-06-15',
                'garansi_bugfix_hingga' => '2027-06-15',
                'status' => 'active',
                'nilai_kontrak' => 6000000.00,
                'catatan_kontrak' => 'Langganan Cloud SaaS Paket Standar 1 Tahun.',
            ]
        );

        $lisensiSma->signed_license_payload = $signer->generateSignedLicensePayload($lisensiSma);
        $lisensiSma->save();

        // 4. Klien 3: SMP Teladan Nusantara (SaaS - Expiring Soon)
        $smp1 = KlienSekolah::firstOrCreate(
            ['npsn' => '20306070'],
            [
                'nama_sekolah' => 'SMP Teladan Nusantara',
                'tipe_sekolah' => 'smp',
                'yayasan_induk' => 'Yayasan Bina Generasi',
                'nama_pic' => 'Ahmad Fadhil, M.Pd',
                'kontak_pic_wa' => '081544332211',
                'email_pic' => 'info@smpteladan.sch.id',
                'provinsi' => 'Jawa Timur',
                'kabupaten_kota' => 'Kota Surabaya',
                'alamat_lengkap' => 'Jl. Pemuda No. 88, Surabaya',
                'status_klien' => 'aktif',
            ]
        );

        $lisensiSmp = Lisensi::firstOrCreate(
            ['nomor_lisensi' => 'LIC-2026-SMP-20306070'],
            [
                'klien_sekolah_id' => $smp1->id,
                'serial_key' => 'AKSR-2026-SMP-1190-Q452',
                'model_lisensi' => 'langganan',
                'tier_paket' => 'lite',
                'token_api' => 'aksr_live_smp20306070_55d91a2bc33e',
                'domain_terdaftar' => 'cbt.smpteladan.sch.id',
                'hardware_fingerprint' => 'NODE-SURABAYA-SMP-01',
                'tanggal_rilis' => '2025-09-10',
                'tanggal_kadaluarsa' => now()->addDays(12)->toDateString(),
                'garansi_bugfix_hingga' => now()->addDays(12)->toDateString(),
                'status' => 'active',
                'nilai_kontrak' => 3500000.00,
                'catatan_kontrak' => 'Langganan Paket Lite 1 Tahun (Masa Aktif Hampir Habis).',
            ]
        );

        $lisensiSmp->signed_license_payload = $signer->generateSignedLicensePayload($lisensiSmp);
        $lisensiSmp->save();

        // 5. Telemetri Heartbeat Logs
        TelemetriHeartbeat::create([
            'lisensi_id' => $lisensiSma->id,
            'ip_address' => '103.144.20.15',
            'domain_terdeteksi' => 'belajar.smapertiwi.sch.id',
            'versi_lms' => '1.0.0',
            'versi_php' => '8.3.6',
            'total_siswa_aktif' => 640,
            'total_guru_aktif' => 42,
            'total_rombel_aktif' => 18,
            'total_ujian_cbt' => 148,
            'db_size_mb' => 24.50,
            'waktu_ping' => now()->subMinutes(15),
        ]);

        TelemetriHeartbeat::create([
            'lisensi_id' => $lisensiSmp->id,
            'ip_address' => '114.122.90.8',
            'domain_terdeteksi' => 'cbt.smpteladan.sch.id',
            'versi_lms' => '1.0.0',
            'versi_php' => '8.2.14',
            'total_siswa_aktif' => 380,
            'total_guru_aktif' => 26,
            'total_rombel_aktif' => 12,
            'total_ujian_cbt' => 84,
            'db_size_mb' => 12.80,
            'waktu_ping' => now()->subHours(2),
        ]);

        // 6. Rilis Pembaruan (Update Registry)
        RilisPembaruan::firstOrCreate(
            ['nomor_versi' => '1.0.1'],
            [
                'tipe_rilis' => 'patch_bugfix',
                'ringkasan_perubahan' => "• Perbaikan konkurensi koneksi WebSocket saat 800+ siswa submit CBT bersamaan.\n• Peningkatan optimasi memori report rekap leger nilai.\n• Patch keamanan input sanitasi pada modul bank soal.",
                'checksum_sha256' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
                'file_signature' => 'RSA_SIGNED_HASH_V1_0_1_VALIDATED',
                'minimal_versi_lms' => '1.0.0',
                'is_public' => true,
                'is_critical_patch' => true,
                'published_at' => now()->subDays(3),
            ]
        );

        // 7. Tiket Dukungan
        TiketDukungan::firstOrCreate(
            ['nomor_tiket' => 'TKT-2026-001'],
            [
                'klien_sekolah_id' => $smk1->id,
                'judul_masalah' => 'Integrasi cetak kartu peserta CBT dengan barcode QR',
                'deskripsi_kendala' => 'Mohon panduan untuk setting format kertas F4 pada cetak massal kartu peserta ujian CBT.',
                'kategori' => 'pertanyaan_fitur',
                'prioritas' => 'sedang',
                'status' => 'in_progress',
                'is_garansi_claim' => true,
                'sla_deadline' => now()->addHours(18),
                'tanggapan_admin' => 'Tim support sedang menyiapkan template PDF F4 sesuai permintaan sekolah.',
            ]
        );

        // 8. Leads Demo
        LeadsDemo::firstOrCreate(
            ['email' => 'kepsek@smkn2malang.sch.id'],
            [
                'nama_pemohon' => 'Bambang Trianto, S.Pd',
                'nama_sekolah' => 'SMK Negeri 2 Malang',
                'tipe_sekolah' => 'smk',
                'nomor_wa' => '081299887711',
                'estimasi_siswa' => 1200,
                'model_minat' => 'beli_putus',
                'url_demo_terbuat' => 'https://demo.aksaraedu.id/smkn-2-malang-x8k',
                'demo_expired_at' => now()->addHours(2),
                'status_followup' => 'baru',
                'catatan_sales' => 'Tertarik paket On-Premise Beli Putus untuk 1.200 siswa.',
            ]
        );

        // 9. Pengumuman Remote Hub
        Pengumuman::firstOrCreate(
            ['judul' => 'Rilis Patch Keamanan & Stabilitas v1.0.1 Tersedia'],
            [
                'pesan' => 'Pembaruan resmi v1.0.1 telah dirilis untuk seluruh instans AksaraEdu. Disarankan segera melakukan update sebelum pelaksanaan Asesmen Semester.',
                'tipe' => 'info',
                'target_model' => 'semua',
                'is_active' => true,
                'mulai_berlaku' => now()->subDay(),
                'selesai_berlaku' => now()->addDays(30),
            ]
        );
    }
}
