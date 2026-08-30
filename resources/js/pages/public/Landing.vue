<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/PublicLayout.vue';
import Button from '../../components/ui/Button.vue';
import Badge from '../../components/ui/Badge.vue';
import Card from '../../components/ui/Card.vue';
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
    Check,
} from 'lucide-vue-next';

interface Props {
    stats: {
        total_sekolah: number;
        total_siswa_terlayani: number;
        provinsi_tercakup: number;
        latest_version: string;
    };
}

defineProps<Props>();

const features = [
    {
        icon: Zap,
        title: 'CBT Engine Super Ringan & Anti-Lag',
        desc: 'Diuji menangani 800+ siswa ujian serentak tanpa lag di server spesifikasi hemat (Dual Core / 4GB RAM).',
    },
    {
        icon: WifiOff,
        title: '100% Offline-Ready (Zero Phoning Home)',
        desc: 'Untuk paket Beli Putus On-Premise, server sekolah berjalan independen tanpa memerlukan koneksi internet sama sekali.',
    },
    {
        icon: BookOpen,
        title: 'Kurikulum Merdeka & K13 Native',
        desc: 'Struktur modul ajar, asesmen diagnostik, formatif, sumatif, serta Capaian Pembelajaran otomatis sesuai standar Kemendikbudristek.',
    },
    {
        icon: QrCode,
        title: 'Presensi QR Anti-Titip Presensi',
        desc: 'Pencatatan kehadiran siswa dan guru real-time berbasis QR Code dinamis dengan deteksi lokasi atau jaringan lokal.',
    },
    {
        icon: FileSpreadsheet,
        title: 'Otomatisasi Leger & e-Rapor',
        desc: 'Rekap nilai ujian dan tugas otomatis terkonversi menjadi format leger dan siap diekspor ke aplikasi Rapor resmi.',
    },
    {
        icon: Lock,
        title: 'Lisensi Kriptografis RSA-4096',
        desc: 'Penerbitan hak cipta dan lisensi terenkripsi asimetris yang menjamin kedaulatan data dan keaslian kepemilikan sekolah.',
    },
];

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
];
</script>

<template>
    <PublicLayout>
        <Head
            title="AksaraEdu - LMS & CBT Lite Anti-Ngadat Khusus SMA, SMK, & SMP"
        />

        <!-- Hero Section -->
        <section
            class="relative overflow-hidden border-b border-slate-800/80 pt-16 pb-24"
        >
            <!-- Glow background decoration -->
            <div
                class="pointer-events-none absolute top-1/4 left-1/2 -z-10 h-[350px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-tr from-emerald-600/20 via-teal-500/15 to-transparent blur-3xl"
            ></div>

            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <!-- Pill Tag -->
                <div
                    class="animate-in fade-in mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-950/60 px-3.5 py-1.5 text-xs font-semibold text-emerald-300"
                >
                    <Sparkles class="h-3.5 w-3.5 text-emerald-400" />
                    <span
                        >Platform LMS & CBT Generasi Baru Khusus Satuan
                        Pendidikan Indonesia</span
                    >
                </div>

                <!-- Headline -->
                <h1
                    class="mx-auto max-w-4xl text-4xl leading-[1.15] font-extrabold tracking-tight text-white sm:text-6xl"
                >
                    LMS & CBT Lite
                    <span
                        class="bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200 bg-clip-text text-transparent"
                        >Anti-Ngadat</span
                    >
                    Khusus SMA, SMK, & SMP
                </h1>

                <!-- Subheadline -->
                <p
                    class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg"
                >
                    Solusi pembelajaran digital dan ujian serentak berstandar
                    Kurikulum Merdeka. Ringan di server hemat, bisa
                    <strong class="text-emerald-300">100% Offline</strong> (Beli
                    Putus), didukung lisensi kriptografi RSA-4096 resmi.
                </p>

                <!-- CTA Buttons -->
                <div
                    class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <Button
                        as="link"
                        href="/demo"
                        variant="primary"
                        size="lg"
                        class="w-full bg-emerald-500 hover:bg-emerald-600 sm:w-auto"
                    >
                        <Zap class="mr-2 h-5 w-5" />
                        Coba Demo 1-Klik Instan
                    </Button>
                    <Button
                        as="link"
                        href="/pricing"
                        variant="outline"
                        size="lg"
                        class="w-full border-slate-700 text-slate-200 hover:border-slate-500 sm:w-auto"
                    >
                        <Calculator class="mr-2 h-5 w-5 text-emerald-400" />
                        Kalkulator & Unduh Penawaran PDF
                    </Button>
                </div>

                <!-- Live Metrics Strip -->
                <div
                    class="mx-auto mt-16 grid max-w-4xl grid-cols-2 gap-4 border-t border-slate-800/80 pt-10 md:grid-cols-4"
                >
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-2xl font-extrabold text-white sm:text-3xl"
                        >
                            {{ stats.total_sekolah }}+
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Sekolah Mitra Terdaftar
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-2xl font-extrabold text-emerald-400 sm:text-3xl"
                        >
                            {{ stats.total_siswa_terlayani.toLocaleString() }}+
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Siswa & Guru Aktif
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-2xl font-extrabold text-teal-300 sm:text-3xl"
                        >
                            {{ stats.provinsi_tercakup }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Provinsi di Indonesia
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-2xl font-extrabold text-sky-400 sm:text-3xl"
                        >
                            v{{ stats.latest_version }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Versi Rilis Stabil
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Model Comparison Section: Beli Putus vs Langganan SaaS -->
        <section class="border-b border-slate-800/80 bg-slate-900/40 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <Badge variant="success" size="md" class="mb-3"
                        >Fleksibilitas Model Lisensi</Badge
                    >
                    <h2
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Pilih Model Lisensi yang Sesuai untuk Anggaran Sekolah
                        Anda
                    </h2>
                    <p class="mt-3 text-sm text-slate-400">
                        AksaraEdu memberikan kebebasan penuh antara kepemilikan
                        On-Premise selamanya atau kemudahan Cloud SaaS tahunan.
                    </p>
                </div>

                <div
                    class="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-2"
                >
                    <!-- Card 1: Beli Putus On-Premise -->
                    <Card
                        class="relative flex flex-col overflow-hidden border-emerald-500/40 bg-slate-900 p-8 shadow-xl"
                    >
                        <div
                            class="absolute top-0 right-0 rounded-bl-xl bg-emerald-500 px-4 py-1.5 text-xs font-extrabold tracking-wider text-slate-950 uppercase"
                        >
                            Favorit Sekolah Mandiri
                        </div>
                        <div class="mb-4 flex items-center gap-3">
                            <div
                                class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-emerald-400"
                            >
                                <Server class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    Beli Putus On-Premise
                                </h3>
                                <p class="text-xs font-medium text-emerald-400">
                                    1x Pembayaran Hak Pakai Selamanya
                                </p>
                            </div>
                        </div>
                        <p class="mb-6 text-xs leading-relaxed text-slate-300">
                            LMS diinstall di server lokal sekolah (Localhost /
                            VM Proxmox). Server tidak wajib terhubung ke
                            internet, bebas biaya tahunan berulang.
                        </p>
                        <ul
                            class="mb-8 flex-1 space-y-3 text-xs text-slate-300"
                        >
                            <li class="flex items-center gap-2.5">
                                <Check
                                    class="h-4 w-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong
                                        >Hak Pakai Seumur Hidup
                                        (Lifetime)</strong
                                    >
                                    untuk NPSN sekolah</span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check
                                    class="h-4 w-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong>Zero Phoning Home:</strong>
                                    Berjalan 100% offline tanpa internet</span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check
                                    class="h-4 w-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong
                                        >Garansi Bugfix 3 Bulan Resmi</strong
                                    >
                                    dengan dukungan tim teknis</span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check
                                    class="h-4 w-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    >File Lisensi Kriptografis Asli
                                    <code>aksaraedu.lic</code></span
                                >
                            </li>
                        </ul>
                        <Button
                            as="link"
                            href="/pricing?model=beli_putus"
                            variant="primary"
                            size="md"
                            class="w-full justify-center bg-emerald-500 hover:bg-emerald-600"
                        >
                            Hitung Biaya Beli Putus & Unduh Penawaran
                        </Button>
                    </Card>

                    <!-- Card 2: Langganan SaaS Cloud -->
                    <Card
                        class="flex flex-col border-slate-700 bg-slate-900 p-8 shadow-xl"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <div
                                class="rounded-xl border border-teal-500/20 bg-teal-500/10 p-3 text-teal-400"
                            >
                                <Cloud class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    Langganan Cloud SaaS
                                </h3>
                                <p class="text-xs font-medium text-teal-400">
                                    Biaya Terjangkau per Tahun
                                </p>
                            </div>
                        </div>
                        <p class="mb-6 text-xs leading-relaxed text-slate-300">
                            Sekolah tidak perlu menyediakan server fisik.
                            Seluruh infrastruktur server, backup otomatis, dan
                            update berkala dikelola oleh tim AksaraEdu.
                        </p>
                        <ul
                            class="mb-8 flex-1 space-y-3 text-xs text-slate-300"
                        >
                            <li class="flex items-center gap-2.5">
                                <Check class="h-4 w-4 shrink-0 text-teal-400" />
                                <span
                                    ><strong>Bebas Maintenance Server:</strong>
                                    Siap pakai 24/7 di Cloud</span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check class="h-4 w-4 shrink-0 text-teal-400" />
                                <span
                                    ><strong>Pembaruan Otomatis:</strong> Selalu
                                    mendapatkan versi rilis terbaru</span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check class="h-4 w-4 shrink-0 text-teal-400" />
                                <span
                                    ><strong
                                        >Garansi Penuh Selama Masa Langganan
                                        Aktif</strong
                                    ></span
                                >
                            </li>
                            <li class="flex items-center gap-2.5">
                                <Check class="h-4 w-4 shrink-0 text-teal-400" />
                                <span
                                    >Domain sekolah kustom (misal:
                                    <code>cbt.namasekolah.sch.id</code>)</span
                                >
                            </li>
                        </ul>
                        <Button
                            as="link"
                            href="/pricing?model=langganan"
                            variant="outline"
                            size="md"
                            class="w-full justify-center border-slate-600 text-slate-200 hover:border-slate-400"
                        >
                            Hitung Biaya Langganan Cloud
                        </Button>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Key Features Grid -->
        <section class="border-b border-slate-800/80 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <Badge variant="info" size="md" class="mb-3"
                        >Fitur Unggulan</Badge
                    >
                    <h2
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Dirancang Khusus Menjawab Masalah Nyata di Sekolah
                    </h2>
                    <p class="mt-3 text-sm text-slate-400">
                        Bukan sekadar LMS biasa, AksaraEdu dioptimalkan secara
                        teknis untuk keandalan CBT massal dan kelengkapan
                        administrasi guru.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="(f, i) in features"
                        :key="i"
                        class="group rounded-2xl border border-slate-800 bg-slate-900/60 p-6 transition-colors hover:border-emerald-500/40"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 transition-transform group-hover:scale-110"
                        >
                            <component :is="f.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mb-2 text-base font-bold text-white">
                            {{ f.title }}
                        </h3>
                        <p class="text-xs leading-relaxed text-slate-400">
                            {{ f.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Head to Head Comparison Table -->
        <section class="border-b border-slate-800/80 bg-slate-900/30 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <Badge variant="warning" size="md" class="mb-3"
                        >Komparasi Solusi</Badge
                    >
                    <h2
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Mengapa Sekolah Memilih AksaraEdu Dibandingkan
                        Alternatif Lain?
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[700px] border-collapse text-left text-xs"
                    >
                        <thead>
                            <tr
                                class="border-b border-slate-800 text-slate-400"
                            >
                                <th
                                    class="w-1/3 px-4 py-4 font-bold tracking-wider uppercase"
                                >
                                    Aspek Perbandingan
                                </th>
                                <th
                                    class="border-x border-emerald-500/20 bg-emerald-950/20 px-4 py-4 font-bold tracking-wider text-emerald-400 uppercase"
                                >
                                    AksaraEdu Hub
                                </th>
                                <th
                                    class="px-4 py-4 font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Moodle Tradisional
                                </th>
                                <th
                                    class="px-4 py-4 font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Google Workspace
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-800/80 text-slate-300"
                        >
                            <tr
                                v-for="(row, idx) in comparison"
                                :key="idx"
                                class="hover:bg-slate-900/40"
                            >
                                <td class="px-4 py-4 font-semibold text-white">
                                    {{ row.feature }}
                                </td>
                                <td
                                    class="flex items-center gap-2 border-x border-emerald-500/20 bg-emerald-950/20 px-4 py-4 font-bold text-emerald-300"
                                >
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-400"
                                    />
                                    {{ row.aksara }}
                                </td>
                                <td class="px-4 py-4 text-slate-400">
                                    {{ row.moodle }}
                                </td>
                                <td class="px-4 py-4 text-slate-400">
                                    {{ row.google }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Bottom CTA Box -->
        <section class="py-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div
                    class="relative overflow-hidden rounded-3xl border border-emerald-500/30 bg-gradient-to-r from-emerald-900/90 via-teal-900/70 to-slate-900 p-8 text-center shadow-2xl sm:p-12"
                >
                    <h2
                        class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                    >
                        Siap Mengupgrade Sistem Pembelajaran & CBT Sekolah Anda?
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-sm text-emerald-100 sm:text-base"
                    >
                        Coba demo instan tanpa ribet atau buat surat penawaran
                        harga resmi (PDF) untuk diajukan dalam rapat anggaran
                        sekolah sekarang.
                    </p>
                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row"
                    >
                        <Button
                            as="link"
                            href="/demo"
                            variant="primary"
                            size="lg"
                            class="bg-white font-bold text-slate-950 shadow-xl hover:bg-slate-100"
                        >
                            Buka Demo Sandbox (2 Jam)
                        </Button>
                        <Button
                            as="link"
                            href="/pricing"
                            variant="outline"
                            size="lg"
                            class="border-emerald-400/40 text-emerald-100 hover:bg-emerald-800/40"
                        >
                            Kalkulator Harga & Unduh Penawaran
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
