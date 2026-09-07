<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../layouts/AdminLayout.vue';
import Card from '../../components/ui/Card.vue';
import Badge from '../../components/ui/Badge.vue';
import Button from '../../components/ui/Button.vue';
import {
    School,
    KeyRound,
    Server,
    Coins,
    AlertTriangle,
    Activity,
    LifeBuoy,
    Users2,
    ShieldCheck,
    ArrowUpRight,
    Package,
    Plus,
    CheckCircle2,
} from 'lucide-vue-next';

interface Props {
    stats: {
        total_klien: number;
        total_beli_putus: number;
        total_langganan: number;
        total_revenue: number;
        total_leads_baru: number;
        total_tiket_open: number;
        active_warranty_count: number;
    };
    expiringLicenses: any[];
    recentTelemetry: any[];
    pendingTickets: any[];
    recentLeads: any[];
}

defineProps<Props>();

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard Eksekutif Vendor - AksaraEdu HQ" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <h1 class="text-base font-bold tracking-tight text-slate-100">
                    Dashboard Operasional Vendor
                </h1>
                <span
                    class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 font-mono text-[10px] text-slate-400"
                >
                    Stage 1 Core
                </span>
            </div>
        </template>

        <!-- Quick Action Bar -->
        <div
            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-4"
        >
            <div>
                <h2 class="text-xs font-bold text-slate-200">
                    Pusat Pengendalian Ekosistem AksaraEdu
                </h2>
                <p class="text-[11px] text-slate-400">
                    Kelola lisensi kriptografis RSA-4096, registrasi sekolah
                    mitra, dan distribusi paket rilis.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Link
                    href="/admin/klien"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-emerald-500"
                >
                    <School class="h-4 w-4" />
                    Kelola Sekolah & Lisensi
                </Link>
                <Link
                    href="/admin/rilis"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700"
                >
                    <Package class="h-3.5 w-3.5 text-indigo-400" />
                    Pembaruan LMS
                </Link>
            </div>
        </div>

        <!-- Top Executive Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Klien -->
            <Card class="border-slate-800 bg-slate-900 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400"
                        >Total Sekolah Mitra</span
                    >
                    <div
                        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2 text-emerald-400"
                    >
                        <School class="h-4 w-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-white">
                    {{ stats.total_klien }}
                </p>
                <div
                    class="mt-1 flex items-center gap-2 text-[11px] text-slate-400"
                >
                    <span class="font-semibold text-emerald-400"
                        >{{ stats.total_beli_putus }} Beli Putus</span
                    >
                    <span>•</span>
                    <span class="font-semibold text-teal-400"
                        >{{ stats.total_langganan }} SaaS Cloud</span
                    >
                </div>
            </Card>

            <!-- Total Revenue -->
            <Card class="border-slate-800 bg-slate-900 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400"
                        >Total Nilai Kontrak</span
                    >
                    <div
                        class="rounded-lg border border-teal-500/20 bg-teal-500/10 p-2 text-teal-400"
                    >
                        <Coins class="h-4 w-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-white">
                    {{ formatCurrency(stats.total_revenue) }}
                </p>
                <p
                    class="mt-1 flex items-center gap-1 text-[11px] text-emerald-400"
                >
                    <ShieldCheck class="h-3 w-3" />
                    {{ stats.active_warranty_count }} Klien dalam Garansi Aktif
                </p>
            </Card>

            <!-- Repositori Rilis -->
            <Card class="border-slate-800 bg-slate-900 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400"
                        >Pusat Rilis & Patch</span
                    >
                    <div
                        class="rounded-lg border border-indigo-500/20 bg-indigo-500/10 p-2 text-indigo-400"
                    >
                        <Package class="h-4 w-4" />
                    </div>
                </div>
                <p class="mt-2 text-lg font-bold text-white">
                    Registry Pembaruan
                </p>
                <Link
                    href="/admin/rilis"
                    class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:underline"
                >
                    Kelola Berkas Rilis & Patch <ArrowUpRight class="h-3 w-3" />
                </Link>
            </Card>

            <!-- Status Engine RSA -->
            <Card class="border-slate-800 bg-slate-900 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400"
                        >Status Otoritas Lisensi</span
                    >
                    <div
                        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2 text-emerald-400"
                    >
                        <KeyRound class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span
                        class="inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-400"
                    ></span>
                    <p class="text-sm font-bold text-white">RSA-4096 Siap</p>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">
                    SHA-256 Asymmetric Engine Aktif
                </p>
            </Card>
        </div>

        <!-- Alert Section: Expiry Watchlist (< 30 Hari) -->
        <div
            v-if="expiringLicenses.length > 0"
            class="rounded-2xl border border-amber-500/40 bg-amber-950/30 p-5"
        >
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <AlertTriangle class="h-4 w-4 text-amber-400" />
                    <h3
                        class="text-xs font-bold tracking-wider text-amber-300 uppercase"
                    >
                        Peringatan Kontrak Mendekati Jatuh Tempo (Expiry
                        Watchlist &lt; 30 Hari)
                    </h3>
                </div>
                <Badge variant="warning"
                    >{{ expiringLicenses.length }} Sekolah</Badge
                >
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div
                    v-for="lic in expiringLicenses"
                    :key="lic.id"
                    class="flex items-center justify-between rounded-xl border border-amber-500/30 bg-slate-900/80 p-3.5"
                >
                    <div>
                        <p class="text-xs font-bold text-white">
                            {{ lic.klien_sekolah?.nama_sekolah }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-amber-400">
                            Jatuh tempo: {{ lic.tanggal_kadaluarsa }} ({{
                                lic.nomor_lisensi
                            }})
                        </p>
                    </div>
                    <Link
                        :href="`/admin/klien/${lic.klien_sekolah_id}`"
                        class="rounded-lg border border-amber-500/40 bg-amber-500/20 px-2.5 py-1 text-xs font-semibold text-amber-300 hover:bg-amber-500/30"
                    >
                        Perpanjang
                    </Link>
                </div>
            </div>
        </div>

        <!-- Two Columns: Telemetry Feed & Quick Operations Summary -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left: Realtime Telemetry Live Heartbeat Feed -->
            <div class="lg:col-span-7">
                <Card
                    class="flex h-full flex-col border-slate-800 bg-slate-900 p-6"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-800 pb-4"
                    >
                        <div class="flex items-center gap-2">
                            <Activity
                                class="h-4 w-4 animate-pulse text-emerald-400"
                            />
                            <h2 class="text-sm font-bold text-white">
                                Live Heartbeat & Telemetri Klien
                            </h2>
                        </div>
                        <Link
                            href="/admin/telemetri"
                            class="text-xs text-emerald-400 hover:underline"
                        >
                            Buka Detail
                        </Link>
                    </div>

                    <div
                        class="mt-2 flex-1 divide-y divide-slate-800/80 overflow-y-auto"
                    >
                        <div
                            v-for="log in recentTelemetry"
                            :key="log.id"
                            class="flex items-center justify-between py-3 text-xs"
                        >
                            <div class="space-y-0.5">
                                <p class="font-semibold text-slate-200">
                                    {{
                                        log.lisensi?.klien_sekolah
                                            ?.nama_sekolah || 'Sekolah Mitra'
                                    }}
                                </p>
                                <div
                                    class="flex items-center gap-2 text-[11px] text-slate-400"
                                >
                                    <span>{{ log.domain_terdeteksi }}</span>
                                    <span>•</span>
                                    <span class="font-mono text-emerald-400"
                                        >v{{ log.versi_lms }}</span
                                    >
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-white">
                                    {{ log.total_siswa_aktif }} Siswa
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    {{
                                        new Date(
                                            log.waktu_ping,
                                        ).toLocaleTimeString('id-ID')
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="recentTelemetry.length === 0"
                            class="py-8 text-center text-xs text-slate-500"
                        >
                            Belum ada log telemetri yang masuk.
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right: Ringkasan Operasional & Tiket/Leads Sekunder -->
            <div class="space-y-6 lg:col-span-5">
                <!-- Info Serah Terima & BAST -->
                <Card class="border-slate-800 bg-slate-900 p-6">
                    <div
                        class="mb-3 flex items-center justify-between border-b border-slate-800 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-emerald-400" />
                            <h2 class="text-sm font-bold text-white">
                                Alur Distribusi Lisensi Sekolah
                            </h2>
                        </div>
                    </div>
                    <div class="space-y-2.5 text-xs text-slate-400">
                        <div class="flex items-start gap-2.5">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-800 text-[10px] font-bold text-emerald-400"
                                >1</span
                            >
                            <p>
                                Daftarkan sekolah di menu
                                <strong>Sekolah</strong> dengan NPSN valid.
                            </p>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-800 text-[10px] font-bold text-emerald-400"
                                >2</span
                            >
                            <p>
                                Lisensi otomatis terbit dan berkas
                                <code>aksaraedu.lic</code> atau bundle
                                <code>.zip</code> langsung dapat diunduh.
                            </p>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-800 text-[10px] font-bold text-emerald-400"
                                >3</span
                            >
                            <p>
                                Serahkan berkas lisensi atau paket siap pasang
                                ke admin sekolah untuk mode Beli Putus / SaaS.
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Helpdesk & Leads Minimal Summary -->
                <Card class="border-slate-800 bg-slate-900 p-6">
                    <div
                        class="mb-3 flex items-center justify-between border-b border-slate-800 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <LifeBuoy class="h-4 w-4 text-amber-400" />
                            <h2 class="text-sm font-bold text-white">
                                Aktivitas Dukungan & Prospek
                            </h2>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <Link
                            href="/admin/tiket"
                            class="rounded-xl border border-slate-800 bg-slate-800/50 p-3 transition-colors hover:bg-slate-800"
                        >
                            <span class="text-[11px] text-slate-400"
                                >Tiket Terbuka</span
                            >
                            <p class="text-lg font-bold text-white">
                                {{ stats.total_tiket_open }}
                            </p>
                        </Link>
                        <Link
                            href="/admin/leads"
                            class="rounded-xl border border-slate-800 bg-slate-800/50 p-3 transition-colors hover:bg-slate-800"
                        >
                            <span class="text-[11px] text-slate-400"
                                >Leads Baru</span
                            >
                            <p class="text-lg font-bold text-white">
                                {{ stats.total_leads_baru }}
                            </p>
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
