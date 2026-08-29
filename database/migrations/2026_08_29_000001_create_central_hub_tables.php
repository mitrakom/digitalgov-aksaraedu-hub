<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter users table to add role, phone, and avatar
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('super_admin')->after('email'); // super_admin, sales, support
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
        });

        // 1. Klien Sekolah
        Schema::create('klien_sekolahs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('npsn', 16)->unique();
            $table->string('nama_sekolah');
            $table->enum('tipe_sekolah', ['sma', 'smk', 'ma', 'mak', 'smp', 'mts'])->default('smk');
            $table->string('yayasan_induk')->nullable();
            $table->string('nama_pic');
            $table->string('kontak_pic_wa');
            $table->string('email_pic');
            $table->string('provinsi');
            $table->string('kabupaten_kota');
            $table->text('alamat_lengkap')->nullable();
            $table->enum('status_klien', ['aktif', 'prospek', 'berhenti'])->default('aktif');
            $table->timestamps();
        });

        // 2. Lisensi
        Schema::create('lisensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('klien_sekolah_id')->constrained('klien_sekolahs')->cascadeOnDelete();
            $table->string('nomor_lisensi')->unique();
            $table->string('serial_key')->unique()->nullable();
            $table->enum('model_lisensi', ['beli_putus', 'langganan'])->default('beli_putus');
            $table->enum('tier_paket', ['lite', 'standar', 'enterprise'])->default('standar');
            $table->string('token_api', 80)->unique();
            $table->longText('signed_license_payload')->nullable();
            $table->string('domain_terdaftar')->nullable();
            $table->string('hardware_fingerprint')->nullable();
            $table->integer('hardware_reset_count')->default(0);
            $table->date('tanggal_rilis');
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->date('garansi_bugfix_hingga')->nullable();
            $table->enum('status', ['active', 'grace_period', 'expired', 'revoked'])->default('active');
            $table->json('allowed_features')->nullable();
            $table->text('catatan_kontrak')->nullable();
            $table->decimal('nilai_kontrak', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Telemetri Heartbeat
        Schema::create('telemetri_heartbeats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lisensi_id')->constrained('lisensis')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('domain_terdeteksi')->nullable();
            $table->string('versi_lms', 30);
            $table->string('versi_php', 20)->nullable();
            $table->integer('total_siswa_aktif')->default(0);
            $table->integer('total_guru_aktif')->default(0);
            $table->integer('total_rombel_aktif')->default(0);
            $table->integer('total_ujian_cbt')->default(0);
            $table->decimal('db_size_mb', 8, 2)->nullable();
            $table->timestamp('waktu_ping');
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Rilis Pembaruan (Update Registry)
        Schema::create('rilis_pembaruans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_versi', 30);
            $table->enum('tipe_rilis', ['patch_bugfix', 'minor_feature', 'major_curriculum'])->default('patch_bugfix');
            $table->text('ringkasan_perubahan');
            $table->string('file_path_zip')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->text('file_signature')->nullable();
            $table->string('minimal_versi_lms', 30)->default('1.0.0');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_critical_patch')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 5. Riwayat Update Download
        Schema::create('riwayat_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rilis_pembaruan_id')->constrained('rilis_pembaruans')->cascadeOnDelete();
            $table->foreignUuid('lisensi_id')->constrained('lisensis')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('downloaded_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });

        // 6. Tiket Dukungan & Klaim Garansi Bugfix
        Schema::create('tiket_dukungans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('klien_sekolah_id')->constrained('klien_sekolahs')->cascadeOnDelete();
            $table->string('nomor_tiket', 32)->unique();
            $table->string('judul_masalah');
            $table->text('deskripsi_kendala');
            $table->enum('kategori', ['bug_sistem', 'pertanyaan_fitur', 'instalasi', 'darurat'])->default('bug_sistem');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'kritis'])->default('sedang');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->boolean('is_garansi_claim')->default(false);
            $table->timestamp('sla_deadline')->nullable();
            $table->text('tanggapan_admin')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 7. Leads Demo (Sales Pipeline CRM)
        Schema::create('leads_demos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_pemohon');
            $table->string('nama_sekolah');
            $table->enum('tipe_sekolah', ['sma', 'smk', 'ma', 'mak', 'smp', 'mts'])->default('smk');
            $table->string('nomor_wa', 25);
            $table->string('email');
            $table->integer('estimasi_siswa')->default(500);
            $table->enum('model_minat', ['beli_putus', 'langganan', 'belum_tahu'])->default('belum_tahu');
            $table->string('url_demo_terbuat')->nullable();
            $table->timestamp('demo_expired_at')->nullable();
            $table->enum('status_followup', ['baru', 'dihubungi', 'presentasi', 'deal', 'lost'])->default('baru');
            $table->text('catatan_sales')->nullable();
            $table->timestamps();
        });

        // 8. Pengumuman / Broadcast Remote ke Klien
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['info', 'warning', 'urgent'])->default('info');
            $table->enum('target_model', ['semua', 'beli_putus', 'langganan'])->default('semua');
            $table->boolean('is_active')->default(true);
            $table->timestamp('mulai_berlaku')->nullable();
            $table->timestamp('selesai_berlaku')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
        Schema::dropIfExists('leads_demos');
        Schema::dropIfExists('tiket_dukungans');
        Schema::dropIfExists('riwayat_updates');
        Schema::dropIfExists('rilis_pembaruans');
        Schema::dropIfExists('telemetri_heartbeats');
        Schema::dropIfExists('lisensis');
        Schema::dropIfExists('klien_sekolahs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'avatar']);
        });
    }
};
