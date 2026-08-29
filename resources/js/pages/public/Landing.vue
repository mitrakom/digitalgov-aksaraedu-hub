<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '../../layouts/PublicLayout.vue'
import Button from '../../components/ui/Button.vue'
import Badge from '../../components/ui/Badge.vue'
import Card from '../../components/ui/Card.vue'
import {
  Sparkles,
  Zap,
  Server,
  Cloud,
  CheckCircle2,
  XCircle,
  Shield,
  BookOpen,
  QrCode,
  FileSpreadsheet,
  ArrowRight,
  Calculator,
  Lock,
  WifiOff,
  Clock,
  Check
} from 'lucide-vue-next'

interface Props {
  stats: {
    total_sekolah: number
    total_siswa_terlayani: number
    provinsi_tercakup: number
    latest_version: string
  }
}

defineProps<Props>()

const features = [
  {
    icon: Zap,
    title: 'CBT Engine Super Ringan & Anti-Lag',
    desc: 'Diuji menangani 800+ siswa ujian serentak tanpa lag di server spesifikasi hemat (Dual Core / 4GB RAM).'
  },
  {
    icon: WifiOff,
    title: '100% Offline-Ready (Zero Phoning Home)',
    desc: 'Untuk paket Beli Putus On-Premise, server sekolah berjalan independen tanpa memerlukan koneksi internet sama sekali.'
  },
  {
    icon: BookOpen,
    title: 'Kurikulum Merdeka & K13 Native',
    desc: 'Struktur modul ajar, asesmen diagnostik, formatif, sumatif, serta Capaian Pembelajaran otomatis sesuai standar Kemendikbudristek.'
  },
  {
    icon: QrCode,
    title: 'Presensi QR Anti-Titip Presensi',
    desc: 'Pencatatan kehadiran siswa dan guru real-time berbasis QR Code dinamis dengan deteksi lokasi atau jaringan lokal.'
  },
  {
    icon: FileSpreadsheet,
    title: 'Otomatisasi Leger & e-Rapor',
    desc: 'Rekap nilai ujian dan tugas otomatis terkonversi menjadi format leger dan siap diekspor ke aplikasi Rapor resmi.'
  },
  {
    icon: Lock,
    title: 'Lisensi Kriptografis RSA-4096',
    desc: 'Penerbitan hak cipta dan lisensi terenkripsi asimetris yang menjamin kedaulatan data dan keaslian kepemilikan sekolah.'
  },
]

const comparison = [
  {
    feature: 'Kebutuhan Server / Hosting',
    aksara: 'Sangat Hemat (Mulai 2 Core / 4GB RAM)',
    moodle: 'Berat & Butuh Server Spek Tinggi',
    google: 'Tergantung Cloud Google Workspace',
  },
  {
    feature: 'Dukungan Ujian Offline (Tanpa Internet)',
    aksara: 'Ya, Full Offline Zero Phoning Home',
    moodle: 'Bisa tapi setup rumit & berat',
    google: 'Tidak bisa (Wajib Internet Cepat)',
  },
  {
    feature: 'Model Kepemilikan Beli Putus (On-Premise)',
    aksara: 'Tersedia (1x Bayar Hak Pakai Selamanya)',
    moodle: 'Gratis Open Source tapi biaya setup/maintenance mahal',
    google: 'Berlangganan / Terikat Ekosistem Google',
  },
  {
    feature: 'Format Penilaian Kurikulum Merdeka',
    aksara: 'Otomatis Terintegrasi Standar Nasional',
    moodle: 'Harus Kustomisasi Rumit Manual',
    google: 'Manual di Google Form / Sheet',
  },
  {
    feature: 'Garansi Bugfix & SLA Bantuan Resmi',
    aksara: 'Garansi 3 Bulan Bugfix SLA 24 Jam',
    moodle: 'Tidak ada (Tergantung Komunitas)',
    google: 'Support Terbatas',
  },
]
</script>

<template>
  <PublicLayout>
    <Head title="AksaraEdu - LMS & CBT Lite Anti-Ngadat Khusus SMA, SMK, & SMP" />

    <!-- Hero Section -->
    <section class="relative pt-16 pb-24 overflow-hidden border-b border-slate-800/80">
      <!-- Glow background decoration -->
      <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-gradient-to-tr from-emerald-600/20 via-teal-500/15 to-transparent blur-3xl -z-10 pointer-events-none rounded-full"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Pill Tag -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-xs font-semibold mb-6 animate-in fade-in">
          <Sparkles class="w-3.5 h-3.5 text-emerald-400" />
          <span>Platform LMS & CBT Generasi Baru Khusus Satuan Pendidikan Indonesia</span>
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-[1.15] max-w-4xl mx-auto">
          LMS & CBT Lite <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200">Anti-Ngadat</span> Khusus SMA, SMK, & SMP
        </h1>

        <!-- Subheadline -->
        <p class="mt-6 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
          Solusi pembelajaran digital dan ujian serentak berstandar Kurikulum Merdeka. Ringan di server hemat, bisa <strong class="text-emerald-300">100% Offline</strong> (Beli Putus), didukung lisensi kriptografi RSA-4096 resmi.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <Button as="link" href="/demo" variant="primary" size="lg" class="w-full sm:w-auto bg-emerald-500 hover:bg-emerald-600">
            <Zap class="w-5 h-5 mr-2" />
            Coba Demo 1-Klik Instan
          </Button>
          <Button as="link" href="/pricing" variant="outline" size="lg" class="w-full sm:w-auto border-slate-700 hover:border-slate-500 text-slate-200">
            <Calculator class="w-5 h-5 mr-2 text-emerald-400" />
            Kalkulator & Unduh Penawaran PDF
          </Button>
        </div>

        <!-- Live Metrics Strip -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto pt-10 border-t border-slate-800/80">
          <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
            <p class="text-2xl sm:text-3xl font-extrabold text-white">{{ stats.total_sekolah }}+</p>
            <p class="text-xs text-slate-400 mt-1">Sekolah Mitra Terdaftar</p>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-400">{{ stats.total_siswa_terlayani.toLocaleString() }}+</p>
            <p class="text-xs text-slate-400 mt-1">Siswa & Guru Aktif</p>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
            <p class="text-2xl sm:text-3xl font-extrabold text-teal-300">{{ stats.provinsi_tercakup }}</p>
            <p class="text-xs text-slate-400 mt-1">Provinsi di Indonesia</p>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
            <p class="text-2xl sm:text-3xl font-extrabold text-sky-400">v{{ stats.latest_version }}</p>
            <p class="text-xs text-slate-400 mt-1">Versi Rilis Stabil</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Model Comparison Section: Beli Putus vs Langganan SaaS -->
    <section class="py-20 bg-slate-900/40 border-b border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
          <Badge variant="success" size="md" class="mb-3">Fleksibilitas Model Lisensi</Badge>
          <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
            Pilih Model Lisensi yang Sesuai untuk Anggaran Sekolah Anda
          </h2>
          <p class="mt-3 text-sm text-slate-400">
            AksaraEdu memberikan kebebasan penuh antara kepemilikan On-Premise selamanya atau kemudahan Cloud SaaS tahunan.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
          <!-- Card 1: Beli Putus On-Premise -->
          <Card class="bg-slate-900 border-emerald-500/40 shadow-xl relative overflow-hidden flex flex-col p-8">
            <div class="absolute top-0 right-0 px-4 py-1.5 bg-emerald-500 text-slate-950 font-extrabold text-xs rounded-bl-xl uppercase tracking-wider">
              Favorit Sekolah Mandiri
            </div>
            <div class="flex items-center gap-3 mb-4">
              <div class="p-3 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <Server class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">Beli Putus On-Premise</h3>
                <p class="text-xs text-emerald-400 font-medium">1x Pembayaran Hak Pakai Selamanya</p>
              </div>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed mb-6">
              LMS diinstall di server lokal sekolah (Localhost / VM Proxmox). Server tidak wajib terhubung ke internet, bebas biaya tahunan berulang.
            </p>
            <ul class="space-y-3 text-xs text-slate-300 mb-8 flex-1">
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                <span><strong>Hak Pakai Seumur Hidup (Lifetime)</strong> untuk NPSN sekolah</span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                <span><strong>Zero Phoning Home:</strong> Berjalan 100% offline tanpa internet</span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                <span><strong>Garansi Bugfix 3 Bulan Resmi</strong> dengan dukungan tim teknis</span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                <span>File Lisensi Kriptografis Asli <code>aksaraedu.lic</code></span>
              </li>
            </ul>
            <Button as="link" href="/pricing?model=beli_putus" variant="primary" size="md" class="w-full justify-center bg-emerald-500 hover:bg-emerald-600">
              Hitung Biaya Beli Putus & Unduh Penawaran
            </Button>
          </Card>

          <!-- Card 2: Langganan SaaS Cloud -->
          <Card class="bg-slate-900 border-slate-700 shadow-xl flex flex-col p-8">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-3 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20">
                <Cloud class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">Langganan Cloud SaaS</h3>
                <p class="text-xs text-teal-400 font-medium">Biaya Terjangkau per Tahun</p>
              </div>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed mb-6">
              Sekolah tidak perlu menyediakan server fisik. Seluruh infrastruktur server, backup otomatis, dan update berkala dikelola oleh tim AksaraEdu.
            </p>
            <ul class="space-y-3 text-xs text-slate-300 mb-8 flex-1">
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-teal-400 shrink-0" />
                <span><strong>Bebas Maintenance Server:</strong> Siap pakai 24/7 di Cloud</span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-teal-400 shrink-0" />
                <span><strong>Pembaruan Otomatis:</strong> Selalu mendapatkan versi rilis terbaru</span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-teal-400 shrink-0" />
                <span><strong>Garansi Penuh Selama Masa Langganan Aktif</strong></span>
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-teal-400 shrink-0" />
                <span>Domain sekolah kustom (misal: <code>cbt.namasekolah.sch.id</code>)</span>
              </li>
            </ul>
            <Button as="link" href="/pricing?model=langganan" variant="outline" size="md" class="w-full justify-center border-slate-600 hover:border-slate-400 text-slate-200">
              Hitung Biaya Langganan Cloud
            </Button>
          </Card>
        </div>
      </div>
    </section>

    <!-- Key Features Grid -->
    <section class="py-20 border-b border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
          <Badge variant="info" size="md" class="mb-3">Fitur Unggulan</Badge>
          <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
            Dirancang Khusus Menjawab Masalah Nyata di Sekolah
          </h2>
          <p class="mt-3 text-sm text-slate-400">
            Bukan sekadar LMS biasa, AksaraEdu dioptimalkan secara teknis untuk keandalan CBT massal dan kelengkapan administrasi guru.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(f, i) in features"
            :key="i"
            class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-emerald-500/40 transition-colors group"
          >
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-5 group-hover:scale-110 transition-transform">
              <component :is="f.icon" class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-white mb-2">{{ f.title }}</h3>
            <p class="text-xs text-slate-400 leading-relaxed">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Head to Head Comparison Table -->
    <section class="py-20 bg-slate-900/30 border-b border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
          <Badge variant="warning" size="md" class="mb-3">Komparasi Solusi</Badge>
          <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
            Mengapa Sekolah Memilih AksaraEdu Dibandingkan Alternatif Lain?
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[700px] text-xs">
            <thead>
              <tr class="border-b border-slate-800 text-slate-400">
                <th class="py-4 px-4 font-bold uppercase tracking-wider w-1/3">Aspek Perbandingan</th>
                <th class="py-4 px-4 font-bold uppercase tracking-wider text-emerald-400 bg-emerald-950/20 border-x border-emerald-500/20">AksaraEdu Hub</th>
                <th class="py-4 px-4 font-bold uppercase tracking-wider text-slate-400">Moodle Tradisional</th>
                <th class="py-4 px-4 font-bold uppercase tracking-wider text-slate-400">Google Workspace</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80 text-slate-300">
              <tr v-for="(row, idx) in comparison" :key="idx" class="hover:bg-slate-900/40">
                <td class="py-4 px-4 font-semibold text-white">{{ row.feature }}</td>
                <td class="py-4 px-4 font-bold text-emerald-300 bg-emerald-950/20 border-x border-emerald-500/20 flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  {{ row.aksara }}
                </td>
                <td class="py-4 px-4 text-slate-400">{{ row.moodle }}</td>
                <td class="py-4 px-4 text-slate-400">{{ row.google }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Bottom CTA Box -->
    <section class="py-20">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-emerald-900/90 via-teal-900/70 to-slate-900 p-8 sm:p-12 border border-emerald-500/30 text-center relative overflow-hidden shadow-2xl">
          <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            Siap Mengupgrade Sistem Pembelajaran & CBT Sekolah Anda?
          </h2>
          <p class="mt-4 text-sm sm:text-base text-emerald-100 max-w-2xl mx-auto">
            Coba demo instan tanpa ribet atau buat surat penawaran harga resmi (PDF) untuk diajukan dalam rapat anggaran sekolah sekarang.
          </p>
          <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button as="link" href="/demo" variant="primary" size="lg" class="bg-white text-slate-950 hover:bg-slate-100 shadow-xl font-bold">
              Buka Demo Sandbox (2 Jam)
            </Button>
            <Button as="link" href="/pricing" variant="outline" size="lg" class="border-emerald-400/40 text-emerald-100 hover:bg-emerald-800/40">
              Kalkulator Harga & Unduh Penawaran
            </Button>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
