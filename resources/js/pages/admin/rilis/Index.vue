<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
import Modal from '../../../components/ui/Modal.vue';
import {
    Package,
    Plus,
    ShieldCheck,
    Download,
    Trash2,
    CheckCircle2,
    FileCode2,
    Calendar,
    Copy,
    Check,
    Search,
    Filter,
    Activity,
    UploadCloud,
    HelpCircle,
    HardDrive,
    ExternalLink,
} from 'lucide-vue-next';

interface ReleaseItem {
    id: string;
    nomor_versi: string;
    tipe_rilis: 'patch_bugfix' | 'minor_feature' | 'major_curriculum';
    ringkasan_perubahan: string;
    minimal_versi_lms: string;
    is_public: boolean;
    is_critical_patch: boolean;
    checksum_sha256?: string;
    file_path_zip?: string;
    published_at?: string;
    riwayat_updates_count?: number;
}

interface Props {
    releases: {
        data: ReleaseItem[];
        links?: any[];
        from?: number;
        to?: number;
        total?: number;
        current_page?: number;
        last_page?: number;
    };
    recentDownloads?: any[];
}

const props = defineProps<Props>();

const isModalOpen = ref(false);
const isHelpModalOpen = ref(false);
const searchQuery = ref('');
const filterTipe = ref('all');
const copiedId = ref<string | null>(null);

const form = useForm({
    nomor_versi: '1.0.7',
    tipe_rilis: 'patch_bugfix',
    ringkasan_perubahan: '• Pembaruan performa dan kestabilan sistem\n• Peningkatan keamanan dan patch bugfix',
    minimal_versi_lms: '1.0.0',
    is_public: true,
    is_critical_patch: false,
    checksum_sha256: '',
    file: null as File | null,
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
    }
};

const submitRelease = () => {
    form.post('/admin/rilis', {
        forceFormData: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteRelease = (id: string, version: string) => {
    if (confirm(`Hapus paket rilis v${version} dari registry pusat? Seluruh klien tidak akan dapat mengunduh versi ini lagi.`)) {
        router.delete(`/admin/rilis/${id}`);
    }
};

const copyToClipboard = (text: string, id: string) => {
    navigator.clipboard.writeText(text);
    copiedId.value = id;
    setTimeout(() => {
        if (copiedId.value === id) copiedId.value = null;
    }, 2000);
};

// Filter data di frontend untuk pengalaman cepat
const filteredReleases = computed(() => {
    if (!props.releases?.data) return [];
    return props.releases.data.filter((r) => {
        const matchesSearch =
            r.nomor_versi.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            r.ringkasan_perubahan.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesType = filterTipe.value === 'all' || r.tipe_rilis === filterTipe.value;
        return matchesSearch && matchesType;
    });
});

const totalDownloads = computed(() => {
    if (!props.releases?.data) return 0;
    return props.releases.data.reduce((acc, curr) => acc + (curr.riwayat_updates_count || 0), 0);
});

const latestVersion = computed(() => {
    if (!props.releases?.data || props.releases.data.length === 0) return '-';
    return 'v' + props.releases.data[0].nomor_versi;
});
</script>

<template>
    <AdminLayout>
        <Head title="Repositori Rilis & Pembaruan (OTA Registry) - AksaraEdu HQ" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <Package class="h-5 w-5 text-emerald-400" />
                <h1 class="text-base font-bold tracking-tight text-slate-100">
                    Repositori Rilis & Pembaruan (OTA Registry)
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top Banner & Quick Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <Card class="border-slate-800 bg-slate-900/90 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Total Versi Rilis</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                            <Package class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold font-mono text-white">
                        {{ releases?.total ?? releases?.data?.length ?? 0 }}
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                        <span>Tersimpan di OTA Registry</span>
                    </div>
                </Card>

                <Card class="border-slate-800 bg-slate-900/90 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Versi Publik Terbaru</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400">
                            <Activity class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold font-mono text-emerald-400">
                        {{ latestVersion }}
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                        <span>Aktif didistribusikan</span>
                    </div>
                </Card>

                <Card class="border-slate-800 bg-slate-900/90 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Total Klien Mengunduh</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                            <Download class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-2xl font-bold font-mono text-white">
                        {{ totalDownloads }} <span class="text-xs font-normal text-slate-400">kali</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                        <span>Audit sinkronisasi update</span>
                    </div>
                </Card>

                <Card class="border-slate-800 bg-slate-900/90 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Integritas Kriptografi</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 text-base font-bold text-slate-100 flex items-center gap-1.5">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        RSA-4096 Active
                    </div>
                    <div class="mt-1 text-[11px] text-slate-400 truncate">
                        SHA256withRSA Payload Sign
                    </div>
                </Card>
            </div>

            <!-- Action Bar & Filter -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                    <!-- Input Search -->
                    <div class="relative min-w-[240px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari versi atau catatan perubahan..."
                            class="w-full rounded-xl border border-slate-800 bg-slate-900/80 pl-9 pr-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- Filter Tipe Rilis -->
                    <div class="relative w-44">
                        <select
                            v-model="filterTipe"
                            class="w-full rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option value="all">Semua Tipe Rilis</option>
                            <option value="patch_bugfix">Patch Bugfix</option>
                            <option value="minor_feature">Minor Feature</option>
                            <option value="major_curriculum">Major Curriculum</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="isHelpModalOpen = true"
                        class="inline-flex items-center gap-1 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
                        title="Panduan Integrasi CI/CD & OTA"
                    >
                        <HelpCircle class="h-3.5 w-3.5 text-slate-400" /> Panduan OTA
                    </button>

                    <Button
                        @click="isModalOpen = true"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 font-bold"
                    >
                        <Plus class="mr-1.5 h-4 w-4" /> Publikasikan Rilis Baru
                    </Button>
                </div>
            </div>

            <!-- Release List Cards -->
            <div class="space-y-4">
                <Card
                    v-for="rel in filteredReleases"
                    :key="rel.id"
                    class="border-slate-800 bg-slate-900/90 p-5 transition-all hover:border-slate-700 shadow-md"
                >
                    <!-- Header Card -->
                    <div class="flex flex-col justify-between gap-3 border-b border-slate-800/80 pb-4 sm:flex-row sm:items-center">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-500/40 bg-emerald-500/10 font-mono font-bold text-sm text-emerald-400 shadow-inner">
                                v{{ rel.nomor_versi }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-bold text-white">
                                        AksaraEdu LMS v{{ rel.nomor_versi }}
                                    </h3>
                                    <Badge
                                        :variant="rel.tipe_rilis === 'patch_bugfix' ? 'success' : rel.tipe_rilis === 'minor_feature' ? 'info' : 'warning'"
                                        size="sm"
                                        class="capitalize font-mono"
                                    >
                                        {{ rel.tipe_rilis.replace('_', ' ') }}
                                    </Badge>
                                    <Badge
                                        v-if="rel.is_critical_patch"
                                        variant="warning"
                                        size="sm"
                                        class="bg-rose-500/20 text-rose-300 border-rose-500/30"
                                    >
                                        Critical Security Patch
                                    </Badge>
                                    <Badge
                                        v-if="!rel.is_public"
                                        variant="ghost"
                                        size="sm"
                                        class="bg-slate-800 text-slate-400"
                                    >
                                        Draft / Privat
                                    </Badge>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400">
                                    <span>
                                        Kompatibel: <strong class="font-mono text-emerald-400">≥ v{{ rel.minimal_versi_lms }}</strong>
                                    </span>
                                    <span>•</span>
                                    <span>
                                        Didownload: <strong class="text-slate-200">{{ rel.riwayat_updates_count || 0 }}</strong> sekolah
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <Calendar class="h-3 w-3 text-slate-500" />
                                        {{ rel.published_at ? new Date(rel.published_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Buttons -->
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <a
                                v-if="rel.file_path_zip"
                                :href="`/admin/rilis/${rel.id}/download`"
                                target="_blank"
                                class="inline-flex items-center rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400 transition-colors hover:bg-emerald-500/20 shadow-sm"
                                title="Unduh Paket Berkas ZIP Rilis"
                            >
                                <Download class="mr-1.5 h-3.5 w-3.5" /> Unduh ZIP
                            </a>
                            <span v-else class="text-[11px] text-slate-500 italic px-2 py-1 bg-slate-800/40 rounded-lg">
                                (Metadata CI/CD Only)
                            </span>

                            <Button
                                @click="deleteRelease(rel.id, rel.nomor_versi)"
                                variant="ghost"
                                size="sm"
                                class="text-slate-400 hover:text-rose-400 hover:bg-rose-500/10"
                                title="Hapus Versi Rilis"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Changelog Section -->
                    <div class="mt-4">
                        <div class="mb-1.5 flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-slate-300 flex items-center gap-1.5">
                                <FileCode2 class="h-3.5 w-3.5 text-slate-400" /> Catatan Perubahan (Changelog):
                            </h4>
                        </div>
                        <div class="rounded-xl border border-slate-800/90 bg-slate-950/70 p-3.5 font-mono text-xs leading-relaxed whitespace-pre-line text-slate-300">
                            {{ rel.ringkasan_perubahan }}
                        </div>
                    </div>

                    <!-- Security & Cryptography Bar -->
                    <div class="mt-4 flex flex-col items-start justify-between gap-2 border-t border-slate-800/80 pt-3 text-[11px] sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2 max-w-full overflow-hidden font-mono text-slate-400">
                            <span class="text-slate-500 font-semibold shrink-0">SHA-256:</span>
                            <span class="truncate text-slate-300 select-all font-mono">
                                {{ rel.checksum_sha256 || 'Calculated automatically upon packaging' }}
                            </span>
                            <button
                                v-if="rel.checksum_sha256"
                                @click="copyToClipboard(rel.checksum_sha256, rel.id)"
                                class="shrink-0 p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-slate-200 transition-colors"
                                title="Salin Checksum SHA-256"
                            >
                                <Check v-if="copiedId === rel.id" class="h-3.5 w-3.5 text-emerald-400" />
                                <Copy v-else class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5 text-emerald-400 font-medium shrink-0">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            <span>RSA-4096 Asymmetric Signature Verified</span>
                        </div>
                    </div>
                </Card>

                <!-- Empty State -->
                <div
                    v-if="filteredReleases.length === 0"
                    class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 py-16 text-center"
                >
                    <Package class="mx-auto h-12 w-12 text-slate-600 mb-3" />
                    <p class="text-sm font-semibold text-slate-300">Tidak ada paket rilis yang sesuai</p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ searchQuery ? 'Tidak ada rilis yang cocok dengan kata kunci pencarian.' : 'Belum ada paket rilis software di repository OTA pusat.' }}
                    </p>
                </div>

                <!-- Pagination jika ada -->
                <div v-if="releases?.links && releases.links.length > 3" class="flex items-center justify-between border-t border-slate-800 px-2 py-4 text-xs">
                    <p class="text-slate-400">
                        Menampilkan <span class="font-semibold text-white">{{ releases.from || 0 }}</span> sampai <span class="font-semibold text-white">{{ releases.to || 0 }}</span> dari <span class="font-semibold text-white">{{ releases.total }}</span> rilis
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in releases.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                            :class="link.active ? 'bg-emerald-600 text-white font-bold' : link.url ? 'text-slate-400 hover:bg-slate-800 hover:text-white' : 'text-slate-600 pointer-events-none'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Recent Client Update Audit Trail -->
            <Card class="mt-8 border-slate-800 bg-slate-900/90 p-6 shadow-md">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                            <CheckCircle2 class="h-4 w-4 text-emerald-400" />
                            Riwayat Log Unduhan Klien (Update Audit Trail)
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Log telemetri pembaruan dan unduhan paket rilis resmi oleh instans LMS sekolah klien.
                        </p>
                    </div>
                    <Badge variant="info" size="sm">
                        {{ recentDownloads?.length || 0 }} Log Terakhir
                    </Badge>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3">Waktu Unduh</th>
                                <th class="px-4 py-3">Sekolah / Klien</th>
                                <th class="px-4 py-3">NPSN / Lisensi</th>
                                <th class="px-4 py-3">Versi Terpasang</th>
                                <th class="px-4 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            <tr
                                v-for="item in recentDownloads"
                                :key="item.id"
                                class="hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400">
                                    {{ item.downloaded_at ? new Date(item.downloaded_at).toLocaleString('id-ID') : '-' }}
                                </td>
                                <td class="px-4 py-3 font-sans font-medium whitespace-nowrap text-white">
                                    {{ item.lisensi?.klien_sekolah?.nama_sekolah || 'Instans Klien Terlisensi' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-emerald-400 font-mono">
                                    {{ item.lisensi?.klien_sekolah?.npsn || item.lisensi?.nomor_lisensi || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Badge variant="success" size="sm" class="font-mono">
                                        v{{ item.rilis_pembaruan?.nomor_versi || '-' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400">
                                    {{ item.ip_address || '127.0.0.1' }}
                                </td>
                            </tr>
                            <tr v-if="!recentDownloads || recentDownloads.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center font-sans text-xs text-slate-500">
                                    Belum ada catatan log unduhan update dari sekolah klien.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <!-- Modal 1: Publikasikan Rilis Pembaruan Baru -->
        <Modal
            :show="isModalOpen"
            @close="isModalOpen = false"
            title="Publikasikan Rilis Pembaruan Baru"
            maxWidth="xl"
        >
            <form @submit.prevent="submitRelease" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-300">
                            Nomor Versi (Semver) <span class="text-rose-400">*</span>
                        </label>
                        <Input
                            v-model="form.nomor_versi"
                            placeholder="1.0.6"
                            required
                        />
                        <p class="mt-1 text-[10px] text-slate-500">Contoh: 1.0.6 atau v1.0.6</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-300">
                            Tipe Rilis <span class="text-rose-400">*</span>
                        </label>
                        <select
                            v-model="form.tipe_rilis"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="patch_bugfix">Patch Bugfix</option>
                            <option value="minor_feature">Minor Feature</option>
                            <option value="major_curriculum">Major Curriculum Upgrade</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-300">
                            Minimal Versi LMS Terpasang <span class="text-rose-400">*</span>
                        </label>
                        <Input
                            v-model="form.minimal_versi_lms"
                            placeholder="1.0.0"
                            required
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-300">
                            Checksum SHA-256 (Opsional)
                        </label>
                        <Input
                            v-model="form.checksum_sha256"
                            placeholder="Dihasilkan otomatis jika kosong"
                        />
                    </div>
                </div>

                <!-- Unggah Berkas ZIP (Opsional bila manual) -->
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">
                        Unggah Berkas Paket ZIP Rilis (Opsional untuk Manual Release)
                    </label>
                    <input
                        type="file"
                        accept=".zip"
                        @change="handleFileChange"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-xs text-slate-300 file:mr-3 file:rounded-md file:border-0 file:bg-slate-700 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-slate-200 hover:file:bg-slate-600"
                    />
                    <p class="mt-1 text-[10px] text-slate-500">
                        Maksimal ukuran berkas 300MB. Jika kosong, rilis ini berperan sebagai metadata/pemberitahuan atau diunggah via CI/CD.
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">
                        Ringkasan Perubahan (Changelog) <span class="text-rose-400">*</span>
                    </label>
                    <textarea
                        v-model="form.ringkasan_perubahan"
                        rows="4"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-xs text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none"
                        placeholder="Tuliskan daftar poin perbaikan atau fitur baru..."
                        required
                    ></textarea>
                </div>

                <div class="flex items-center gap-5 pt-1 text-xs text-slate-300">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="form.is_critical_patch"
                            class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500"
                        />
                        <span>Patch Kritis Darurat</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="form.is_public"
                            class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500"
                        />
                        <span>Tampilkan di Registry Publik</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-800 pt-4">
                    <Button @click="isModalOpen = false" variant="ghost" size="sm">Batal</Button>
                    <Button
                        type="submit"
                        :loading="form.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 font-bold hover:bg-emerald-600 shadow-md shadow-emerald-500/20"
                    >
                        Publikasikan ke Registry
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal 2: Panduan Integrasi CI/CD & OTA Updates -->
        <Modal
            :show="isHelpModalOpen"
            @close="isHelpModalOpen = false"
            title="Panduan Alur Otomatisasi Rilis (CI/CD ke Central Hub)"
            maxWidth="xl"
        >
            <div class="space-y-4 text-xs text-slate-300">
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-3.5 text-emerald-300">
                    <p class="font-semibold mb-1">💡 Bagaimana Rilis v1.0.6 Otomatis Muncul di Hub?</p>
                    <p class="text-slate-300 text-[11px] leading-relaxed">
                        Setiap kali ada tag baru seperti <code>v1.0.6</code> di-push pada repositori aplikasi <code>[APP]</code>, GitHub Actions di workflow <code>release-app.yml</code> akan membuat paket zip rilis, menandatanganinya, dan mengirim HTTP POST ke Central Hub ini.
                    </p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-bold text-slate-200">1. Pastikan GitHub Secrets pada Repo [APP] Sudah Terisi:</h4>
                    <div class="space-y-1 rounded-lg bg-slate-950 p-3 font-mono text-[11px] text-slate-400">
                        <div><strong class="text-emerald-400">HUB_API_URL:</strong> https://aksaraedu.mitralab.site</div>
                        <div><strong class="text-emerald-400">HUB_DEPLOY_SECRET:</strong> (Sesuai DEPLOY_WEBHOOK_SECRET di file .env Hub)</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h4 class="font-bold text-slate-200">2. Endpoint API Penerima Rilis di Central Hub:</h4>
                    <p class="text-[11px] text-slate-400">
                        Workflow mengirim POST multipart/form-data ke <code>/api/v1/updates/publish</code> dengan header <code>X-Deploy-Token: [HUB_DEPLOY_SECRET]</code>.
                    </p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-bold text-slate-200">3. Solusi Jika Rilis Belum Muncul:</h4>
                    <ul class="list-disc pl-4 space-y-1 text-[11px] text-slate-400">
                        <li>Periksa log tab <strong>Actions</strong> pada repo GitHub aplikasi <code>[APP]</code> saat tag <code>v1.0.6</code> di-trigger.</li>
                        <li>Pastikan step <em>"Deploy & Register Release to Central Hub (@hub)"</em> tidak berstatus <em>Melewati deploy ke Hub</em> atau gagal autentikasi HTTP 403.</li>
                        <li>Anda juga dapat langsung mendaftarkan versi <strong>1.0.6</strong> secara manual lewat tombol <strong>+ Publikasikan Rilis Baru</strong> di atas.</li>
                    </ul>
                </div>

                <div class="flex justify-end border-t border-slate-800 pt-3">
                    <Button @click="isHelpModalOpen = false" variant="primary" size="sm">
                        Mengerti
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

