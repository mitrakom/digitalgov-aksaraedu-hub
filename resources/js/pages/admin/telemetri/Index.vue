<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
import {
    Activity,
    Search,
    Users,
    GraduationCap,
    FileCheck2,
    Server,
    Clock,
    Radio,
} from 'lucide-vue-next';

interface Props {
    logs: any;
    filters: any;
    summary: {
        total_siswa: number;
        total_guru: number;
        total_ujian: number;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(
        '/admin/telemetri',
        { search: search.value },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <AdminLayout>
        <Head title="Monitor Telemetri & Heartbeat Realtime - AksaraEdu HQ" />

        <template #header-title>
            <h1 class="text-base font-bold tracking-tight text-slate-100">
                Monitor Telemetri & Heartbeat Realtime
            </h1>
        </template>

        <div class="space-y-6">
            <!-- Aggregated Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card class="border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400"
                            >Total Siswa Aktif Terpantau</span
                        >
                        <div
                            class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2 text-emerald-400"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-white">
                        {{ summary.total_siswa.toLocaleString() }}
                    </p>
                    <p class="mt-1 text-[11px] text-emerald-400">
                        Data agregat telemetri instans klien
                    </p>
                </Card>

                <Card class="border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400"
                            >Total Tenaga Pendidik (Guru)</span
                        >
                        <div
                            class="rounded-lg border border-teal-500/20 bg-teal-500/10 p-2 text-teal-400"
                        >
                            <GraduationCap class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-white">
                        {{ summary.total_guru.toLocaleString() }}
                    </p>
                    <p class="mt-1 text-[11px] text-teal-400">
                        Guru pengampu & wali kelas aktif
                    </p>
                </Card>

                <Card class="border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400"
                            >Sesi Ujian CBT Dijalankan</span
                        >
                        <div
                            class="rounded-lg border border-sky-500/20 bg-sky-500/10 p-2 text-sky-400"
                        >
                            <FileCheck2 class="h-4 w-4" />
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-white">
                        {{ summary.total_ujian.toLocaleString() }}
                    </p>
                    <p class="mt-1 text-[11px] text-sky-400">
                        Akumulasi asesmen formatif & sumatif
                    </p>
                </Card>
            </div>

            <!-- Search Bar -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <Input
                        v-model="search"
                        placeholder="Cari berdasarkan sekolah, IP address, domain, atau versi LMS..."
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button @click="handleSearch" variant="secondary" size="sm">
                    <Search class="mr-1 h-3.5 w-3.5" /> Cari
                </Button>
            </div>

            <!-- Heartbeat Log Table -->
            <Card class="border-slate-800 bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/60 font-semibold tracking-wider text-slate-400 uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Sekolah / Instans</th>
                                <th class="px-4 py-3">Domain & IP</th>
                                <th class="px-4 py-3">Versi Software</th>
                                <th class="px-4 py-3">Metrik Penggunaan</th>
                                <th class="px-4 py-3 text-right">
                                    Waktu Ping (Heartbeat)
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-800/60 text-slate-300"
                        >
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="transition-colors hover:bg-slate-800/40"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-bold text-white">
                                        {{
                                            log.lisensi?.klien_sekolah
                                                ?.nama_sekolah ||
                                            'Instans Terdaftar'
                                        }}
                                    </p>
                                    <p
                                        class="font-mono text-[10px] text-emerald-400"
                                    >
                                        NPSN:
                                        {{ log.lisensi?.klien_sekolah?.npsn }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-200">
                                        {{ log.domain_terdeteksi }}
                                    </p>
                                    <p
                                        class="font-mono text-[11px] text-slate-500"
                                    >
                                        {{ log.ip_address }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        variant="success"
                                        size="sm"
                                        class="font-mono"
                                        >LMS v{{ log.versi_lms }}</Badge
                                    >
                                    <p
                                        v-if="log.versi_php"
                                        class="mt-0.5 text-[10px] text-slate-400"
                                    >
                                        PHP {{ log.versi_php }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-0.5">
                                        <p class="font-semibold text-white">
                                            {{ log.total_siswa_aktif }} Siswa |
                                            {{ log.total_guru_aktif }} Guru
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ log.total_rombel_aktif }} Rombel
                                            | {{ log.total_ujian_cbt }} CBT
                                            Tests
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div
                                        class="inline-flex items-center gap-1 text-slate-300"
                                    >
                                        <Clock
                                            class="h-3 w-3 text-emerald-400"
                                        />
                                        <span>{{
                                            new Date(
                                                log.waktu_ping,
                                            ).toLocaleString('id-ID')
                                        }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td
                                    colspan="5"
                                    class="py-8 text-center text-xs text-slate-500"
                                >
                                    Tidak ada rekaman telemetri yang sesuai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
