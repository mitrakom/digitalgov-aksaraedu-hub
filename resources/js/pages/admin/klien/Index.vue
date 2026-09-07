<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
import Modal from '../../../components/ui/Modal.vue';
import {
    School,
    Plus,
    Search,
    ChevronRight,
    MapPin,
    Phone,
    Mail,
    Download,
    Package,
    KeyRound,
    RotateCcw,
    Copy,
    CheckCircle2,
    Calendar,
    Globe,
    Cpu,
} from 'lucide-vue-next';

interface Props {
    kliens: any;
    filters: any;
    publicKey?: string;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const tipeSekolah = ref(props.filters.tipe_sekolah || '');
const statusKlien = ref(props.filters.status_klien || '');

// Modals state
const isModalTambahOpen = ref(false);
const isModalPublicKeyOpen = ref(false);
const isModalTerbitkanOpen = ref(false);
const isModalRenewOpen = ref(false);
const selectedKlien = ref<any>(null);
const selectedLicense = ref<any>(null);
const copySuccess = ref(false);

// Form Tambah Sekolah (Terintegrasi Lisensi)
const formTambah = useForm({
    npsn: '',
    nama_sekolah: '',
    tipe_sekolah: 'smk',
    yayasan_induk: '',
    nama_pic: '',
    kontak_pic_wa: '',
    email_pic: '',
    provinsi: 'Jawa Barat',
    kabupaten_kota: 'Kota Bandung',
    alamat_lengkap: '',
    status_klien: 'aktif',
    // Lisensi Terpadu
    buat_lisensi: true,
    model_lisensi: 'beli_putus',
    tier_paket: 'enterprise',
    domain_terdaftar: '',
    durasi_bulan: 12,
    garansi_bulan: 3,
    nilai_kontrak: 15000000,
    catatan_kontrak: '',
});

// Form Terbitkan Lisensi Tambahan
const formLisensi = useForm({
    model_lisensi: 'beli_putus',
    tier_paket: 'enterprise',
    domain_terdaftar: '',
    durasi_bulan: 12,
    garansi_bulan: 3,
    nilai_kontrak: 15000000,
    catatan_kontrak: '',
});

// Form Perpanjang Lisensi
const formRenew = useForm({
    perpanjang_bulan: 12,
    nilai_kontrak_tambahan: 6000000,
});

const handleFilter = () => {
    router.get(
        '/admin/klien',
        {
            search: search.value,
            tipe_sekolah: tipeSekolah.value,
            status_klien: statusKlien.value,
        },
        { preserveState: true, replace: true },
    );
};

const submitTambahSekolah = () => {
    formTambah.post('/admin/klien', {
        onSuccess: () => {
            isModalTambahOpen.value = false;
            formTambah.reset();
            formTambah.buat_lisensi = true;
        },
    });
};

const openTerbitkanModal = (klien: any) => {
    selectedKlien.value = klien;
    formLisensi.reset();
    isModalTerbitkanOpen.value = true;
};

const submitTerbitkanLisensi = () => {
    if (!selectedKlien.value) return;
    formLisensi.post(`/admin/klien/${selectedKlien.value.id}/lisensi`, {
        onSuccess: () => {
            isModalTerbitkanOpen.value = false;
        },
    });
};

const openRenewModal = (lic: any) => {
    selectedLicense.value = lic;
    isModalRenewOpen.value = true;
};

const submitRenew = () => {
    if (!selectedLicense.value) return;
    formRenew.post(`/admin/lisensi/${selectedLicense.value.id}/renew`, {
        onSuccess: () => {
            isModalRenewOpen.value = false;
        },
    });
};

const resetHardware = (licId: string) => {
    if (
        confirm(
            'Reset pengikatan Hardware Fingerprint? Server sekolah dapat melakukan binding ulang pada perangkat baru.',
        )
    ) {
        router.post(`/admin/lisensi/${licId}/reset-hardware`);
    }
};

const copyKey = () => {
    if (!props.publicKey) return;
    navigator.clipboard.writeText(props.publicKey);
    copySuccess.value = true;
    setTimeout(() => {
        copySuccess.value = false;
    }, 2000);
};

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
        <Head title="Sekolah & Lisensi - AksaraEdu HQ" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <h1 class="text-base font-bold tracking-tight text-slate-100">
                    Sekolah
                </h1>
                <span
                    class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 text-[11px] text-slate-400"
                >
                    Data Sekolah & Lisensi
                </span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top Action & Filter Bar -->
            <div
                class="flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div class="flex flex-1 flex-col gap-2.5 sm:flex-row">
                    <div class="relative flex-1">
                        <Input
                            v-model="search"
                            placeholder="Cari nama sekolah, NPSN, PIC, kota..."
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <select
                        v-model="tipeSekolah"
                        @change="handleFilter"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200"
                    >
                        <option value="">Semua Jenjang</option>
                        <option value="smk">SMK</option>
                        <option value="sma">SMA</option>
                        <option value="ma">MA</option>
                        <option value="mak">MAK</option>
                        <option value="smp">SMP</option>
                        <option value="mts">MTs</option>
                    </select>
                    <select
                        v-model="statusKlien"
                        @change="handleFilter"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="prospek">Prospek</option>
                        <option value="berhenti">Berhenti</option>
                    </select>
                    <Button @click="handleFilter" variant="secondary" size="sm">
                        <Search class="mr-1 h-3.5 w-3.5" /> Filter
                    </Button>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="publicKey"
                        @click="isModalPublicKeyOpen = true"
                        variant="outline"
                        size="sm"
                        class="border-slate-700 text-slate-300 hover:text-white"
                        title="Lihat Public Key RSA Otoritas"
                    >
                        <KeyRound class="mr-1.5 h-3.5 w-3.5 text-emerald-400" />
                        Kunci RSA
                    </Button>

                    <Button
                        @click="isModalTambahOpen = true"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        <Plus class="mr-1.5 h-4 w-4" /> Tambah Sekolah
                    </Button>
                </div>
            </div>

            <!-- School & License Unified Table -->
            <Card class="border-slate-800 bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/60 font-semibold tracking-wider text-slate-400 uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Sekolah</th>
                                <th class="px-4 py-3">Kontak PIC</th>
                                <th class="px-4 py-3">Lisensi & Masa Aktif</th>
                                <th class="px-4 py-3">Aksi Cepat</th>
                                <th class="px-4 py-3 text-right">Navigasi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-800/60 text-slate-300"
                        >
                            <tr
                                v-for="klien in kliens.data"
                                :key="klien.id"
                                class="transition-colors hover:bg-slate-800/40"
                            >
                                <!-- Kolom 1: Profil Sekolah -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-start gap-2.5">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-emerald-500/20 bg-emerald-500/10 text-emerald-400"
                                        >
                                            <School class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-white"
                                            >
                                                {{ klien.nama_sekolah }}
                                            </p>
                                            <div
                                                class="mt-0.5 flex items-center gap-2 text-[11px]"
                                            >
                                                <span
                                                    class="font-mono font-semibold text-emerald-400"
                                                >
                                                    NPSN: {{ klien.npsn }}
                                                </span>
                                                <span class="text-slate-500"
                                                    >•</span
                                                >
                                                <span
                                                    class="font-semibold text-slate-300 uppercase"
                                                >
                                                    {{ klien.tipe_sekolah }}
                                                </span>
                                                <span class="text-slate-500"
                                                    >•</span
                                                >
                                                <span class="text-slate-400">
                                                    {{ klien.kabupaten_kota }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom 2: Kontak PIC -->
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-200">
                                        {{ klien.nama_pic }}
                                    </p>
                                    <div
                                        class="mt-0.5 flex items-center gap-2 text-[11px] text-slate-400"
                                    >
                                        <span>{{ klien.kontak_pic_wa }}</span>
                                    </div>
                                    <p
                                        class="truncate text-[10px] text-slate-500"
                                    >
                                        {{ klien.email_pic }}
                                    </p>
                                </td>

                                <!-- Kolom 3: Lisensi Terkini -->
                                <td class="px-4 py-3.5">
                                    <div
                                        v-if="
                                            klien.lisensis &&
                                            klien.lisensis.length > 0
                                        "
                                    >
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                :variant="
                                                    klien.lisensis[0]
                                                        .model_lisensi ===
                                                    'beli_putus'
                                                        ? 'success'
                                                        : 'info'
                                                "
                                                size="sm"
                                            >
                                                {{
                                                    klien.lisensis[0]
                                                        .model_lisensi ===
                                                    'beli_putus'
                                                        ? 'Beli Putus'
                                                        : 'SaaS Cloud'
                                                }}
                                            </Badge>
                                            <span
                                                class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase"
                                                :class="
                                                    klien.lisensis[0].status ===
                                                    'active'
                                                        ? 'bg-emerald-500/10 text-emerald-400'
                                                        : 'bg-amber-500/10 text-amber-400'
                                                "
                                            >
                                                {{ klien.lisensis[0].status }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-mono text-[11px] text-slate-300"
                                        >
                                            {{
                                                klien.lisensis[0].nomor_lisensi
                                            }}
                                        </p>

                                        <p
                                            v-if="
                                                klien.lisensis[0]
                                                    .model_lisensi ===
                                                    'langganan' &&
                                                klien.lisensis[0]
                                                    .tanggal_kadaluarsa
                                            "
                                            class="text-[10px] text-slate-400"
                                        >
                                            Berlaku s/d
                                            {{
                                                klien.lisensis[0]
                                                    .tanggal_kadaluarsa
                                            }}
                                        </p>
                                        <p
                                            v-else
                                            class="text-[10px] text-emerald-400/80"
                                        >
                                            Lisensi Permanen (Offline Ready)
                                        </p>
                                    </div>
                                    <div v-else class="space-y-1">
                                        <span
                                            class="text-[11px] text-slate-500 italic"
                                            >Belum ada lisensi</span
                                        >
                                        <div>
                                            <button
                                                @click="
                                                    openTerbitkanModal(klien)
                                                "
                                                class="inline-flex items-center text-[11px] font-semibold text-emerald-400 hover:underline"
                                            >
                                                + Terbitkan Lisensi
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom 4: Aksi Cepat Lisensi -->
                                <td class="px-4 py-3.5">
                                    <div
                                        v-if="
                                            klien.lisensis &&
                                            klien.lisensis.length > 0
                                        "
                                        class="flex flex-wrap items-center gap-1.5"
                                    >
                                        <!-- Unduh Berkas .lic -->
                                        <a
                                            :href="`/admin/lisensi/${klien.lisensis[0].id}/download`"
                                            class="inline-flex items-center gap-1 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-500/20"
                                            title="Unduh berkas lisensi resmi aksaraedu.lic"
                                        >
                                            <Download class="h-3 w-3" /> .lic
                                        </a>

                                        <!-- Unduh Bundle Siap Pasang .zip -->
                                        <a
                                            :href="`/admin/lisensi/${klien.lisensis[0].id}/download-bundle`"
                                            class="inline-flex items-center gap-1 rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-2 py-1 text-[11px] font-semibold text-indigo-300 hover:bg-indigo-500/20"
                                            title="Unduh paket ZIP LMS terkonfigurasi siap pasang"
                                        >
                                            <Package class="h-3 w-3" /> Bundle
                                        </a>

                                        <!-- Perpanjang (Khusus SaaS) -->
                                        <button
                                            v-if="
                                                klien.lisensis[0]
                                                    .model_lisensi ===
                                                'langganan'
                                            "
                                            @click="
                                                openRenewModal(
                                                    klien.lisensis[0],
                                                )
                                            "
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 px-2 py-1 text-[11px] font-semibold text-slate-300 hover:bg-slate-700"
                                            title="Perpanjang masa aktif lisensi"
                                        >
                                            Perpanjang
                                        </button>

                                        <!-- Reset Hardware -->
                                        <button
                                            v-if="
                                                klien.lisensis[0]
                                                    .hardware_fingerprint
                                            "
                                            @click="
                                                resetHardware(
                                                    klien.lisensis[0].id,
                                                )
                                            "
                                            class="inline-flex items-center rounded-lg border border-slate-700 bg-slate-800 p-1 text-[11px] text-slate-400 hover:text-amber-400"
                                            title="Reset pengikatan Hardware Fingerprint"
                                        >
                                            <RotateCcw class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <div v-else>
                                        <span class="text-[11px] text-slate-500"
                                            >-</span
                                        >
                                    </div>
                                </td>

                                <!-- Kolom 5: Detail & Navigasi -->
                                <td class="px-4 py-3.5 text-right">
                                    <Link
                                        :href="`/admin/klien/${klien.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 transition-colors hover:bg-slate-700 hover:text-white"
                                    >
                                        Detail
                                        <ChevronRight class="h-3.5 w-3.5" />
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="kliens.data.length === 0">
                                <td
                                    colspan="5"
                                    class="py-12 text-center text-xs text-slate-500"
                                >
                                    Belum ada data sekolah yang terdaftar. Klik
                                    tombol
                                    <strong>+ Tambah Sekolah</strong> untuk
                                    memulai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links jika ada -->
                <div
                    v-if="kliens.links && kliens.links.length > 3"
                    class="flex items-center justify-between border-t border-slate-800 px-4 py-3 text-xs"
                >
                    <p class="text-slate-400">
                        Menampilkan
                        <span class="font-semibold text-white">{{
                            kliens.from || 0
                        }}</span>
                        sampai
                        <span class="font-semibold text-white">{{
                            kliens.to || 0
                        }}</span>
                        dari
                        <span class="font-semibold text-white">{{
                            kliens.total
                        }}</span>
                        sekolah
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in kliens.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-md px-2.5 py-1 text-xs transition-colors"
                            :class="
                                link.active
                                    ? 'bg-emerald-600 font-bold text-white'
                                    : link.url
                                      ? 'text-slate-400 hover:bg-slate-800 hover:text-white'
                                      : 'pointer-events-none text-slate-600'
                            "
                            v-html="link.label"
                        />
                    </div>
                </div>
            </Card>
        </div>

        <!-- Modal 1: Tambah Sekolah Baru (Terintegrasi Pembuatan Lisensi Sekaligus) -->
        <Modal
            :show="isModalTambahOpen"
            @close="isModalTambahOpen = false"
            title="Tambah Sekolah & Terbitkan Lisensi"
            maxWidth="xl"
        >
            <form @submit.prevent="submitTambahSekolah" class="space-y-4">
                <!-- Bagian 1: Identitas Sekolah -->
                <div class="space-y-3">
                    <h3
                        class="text-xs font-bold tracking-wide text-slate-300 uppercase"
                    >
                        1. Data Sekolah
                    </h3>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >NPSN (8 Digit)</label
                            >
                            <Input
                                v-model="formTambah.npsn"
                                placeholder="Contoh: 20104050"
                                :error="formTambah.errors.npsn"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >Jenjang</label
                            >
                            <select
                                v-model="formTambah.tipe_sekolah"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                            >
                                <option value="smk">SMK (Kejuruan)</option>
                                <option value="sma">SMA</option>
                                <option value="ma">MA</option>
                                <option value="mak">MAK</option>
                                <option value="smp">SMP</option>
                                <option value="mts">MTs</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Nama Sekolah</label
                        >
                        <Input
                            v-model="formTambah.nama_sekolah"
                            placeholder="Contoh: SMK Negeri 1 Kota Bandung"
                            :error="formTambah.errors.nama_sekolah"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >Nama PIC / Penanggung Jawab</label
                            >
                            <Input
                                v-model="formTambah.nama_pic"
                                placeholder="Contoh: Drs. H. Mulyadi, M.Kom"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >No. WhatsApp PIC</label
                            >
                            <Input
                                v-model="formTambah.kontak_pic_wa"
                                placeholder="Contoh: 081234567890"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >Email Resmi</label
                            >
                            <Input
                                v-model="formTambah.email_pic"
                                type="email"
                                placeholder="admin@smkn1bdg.sch.id"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-300"
                                >Kota / Kabupaten</label
                            >
                            <Input
                                v-model="formTambah.kabupaten_kota"
                                placeholder="Kota Bandung"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Lisensi Terpadu -->
                <div
                    class="space-y-3 rounded-xl border border-slate-800 bg-slate-950/60 p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <KeyRound class="h-4 w-4 text-emerald-400" />
                            <h3
                                class="text-xs font-bold tracking-wide text-white uppercase"
                            >
                                2. Penerbitan Lisensi Otomatis
                            </h3>
                        </div>
                        <label
                            class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-300"
                        >
                            <input
                                type="checkbox"
                                v-model="formTambah.buat_lisensi"
                                class="rounded border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500"
                            />
                            <span>Terbitkan Sekarang</span>
                        </label>
                    </div>

                    <div
                        v-if="formTambah.buat_lisensi"
                        class="grid grid-cols-1 gap-3 border-t border-slate-800 pt-2 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-400"
                                >Model Lisensi</label
                            >
                            <select
                                v-model="formTambah.model_lisensi"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                            >
                                <option value="beli_putus">
                                    Beli Putus (On-Premise / 100% Offline)
                                </option>
                                <option value="langganan">
                                    Berlangganan (SaaS Cloud)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-400"
                            >
                                {{
                                    formTambah.model_lisensi === 'langganan'
                                        ? 'Durasi Langganan (Bulan)'
                                        : 'Masa Garansi Bugfix (Bulan)'
                                }}
                            </label>
                            <Input
                                v-if="formTambah.model_lisensi === 'langganan'"
                                v-model="formTambah.durasi_bulan"
                                type="number"
                                placeholder="12"
                            />
                            <Input
                                v-else
                                v-model="formTambah.garansi_bulan"
                                type="number"
                                placeholder="3"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-400"
                                >Domain Terdaftar (Opsional)</label
                            >
                            <Input
                                v-model="formTambah.domain_terdaftar"
                                placeholder="lms.smkn1bdg.sch.id"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-400"
                                >Nilai Kontrak (Rp)</label
                            >
                            <Input
                                v-model="formTambah.nilai_kontrak"
                                type="number"
                                placeholder="15000000"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        type="button"
                        @click="isModalTambahOpen = false"
                        variant="ghost"
                        size="sm"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :loading="formTambah.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        Simpan & Terbitkan
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal 2: Kunci Publik RSA (Otoritas Hub) -->
        <Modal
            :show="isModalPublicKeyOpen"
            @close="isModalPublicKeyOpen = false"
            title="Kunci Publik RSA-4096 (Hub Authority)"
            maxWidth="lg"
        >
            <div class="space-y-4 text-xs text-slate-300">
                <p>
                    Kunci publik ini digunakan pada aplikasi klien sekolah
                    (<code class="text-emerald-400">AksaraEdu LMS Instance</code
                    >) untuk memverifikasi keaslian dan integritas payload
                    berkas lisensi secara offline.
                </p>

                <div class="relative">
                    <textarea
                        readonly
                        :value="publicKey"
                        rows="10"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 p-3 font-mono text-[11px] text-slate-300 select-all focus:outline-none"
                    ></textarea>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-800 pt-3"
                >
                    <span
                        v-if="copySuccess"
                        class="flex items-center gap-1.5 text-xs text-emerald-400"
                    >
                        <CheckCircle2 class="h-4 w-4" /> Kunci publik berhasil
                        disalin!
                    </span>
                    <span v-else class="text-[11px] text-slate-500">
                        Ditanam secara otomatis pada instance rilis.
                    </span>

                    <Button
                        @click="copyKey"
                        variant="secondary"
                        size="sm"
                        class="bg-slate-800 hover:bg-slate-700"
                    >
                        <Copy class="mr-1.5 h-3.5 w-3.5" /> Salin Kunci Publik
                    </Button>
                </div>
            </div>
        </Modal>

        <!-- Modal 3: Terbitkan Lisensi Cepat untuk Sekolah Terpilih -->
        <Modal
            :show="isModalTerbitkanOpen"
            @close="isModalTerbitkanOpen = false"
            :title="`Terbitkan Lisensi: ${selectedKlien?.nama_sekolah || ''}`"
            maxWidth="md"
        >
            <form @submit.prevent="submitTerbitkanLisensi" class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-300"
                        >Model Lisensi</label
                    >
                    <select
                        v-model="formLisensi.model_lisensi"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                    >
                        <option value="beli_putus">
                            Beli Putus (On-Premise 100% Offline)
                        </option>
                        <option value="langganan">
                            Berlangganan (SaaS Cloud)
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-300"
                            >Tier Paket</label
                        >
                        <select
                            v-model="formLisensi.tier_paket"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="lite">Lite</option>
                            <option value="standar">Standar</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-300"
                        >
                            {{
                                formLisensi.model_lisensi === 'langganan'
                                    ? 'Durasi (Bulan)'
                                    : 'Garansi (Bulan)'
                            }}
                        </label>
                        <Input
                            v-if="formLisensi.model_lisensi === 'langganan'"
                            v-model="formLisensi.durasi_bulan"
                            type="number"
                            placeholder="12"
                        />
                        <Input
                            v-else
                            v-model="formLisensi.garansi_bulan"
                            type="number"
                            placeholder="3"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-300"
                        >Domain Terdaftar (Opsional)</label
                    >
                    <Input
                        v-model="formLisensi.domain_terdaftar"
                        placeholder="lms.sekolah.sch.id"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-300"
                        >Nilai Kontrak (Rp)</label
                    >
                    <Input
                        v-model="formLisensi.nilai_kontrak"
                        type="number"
                        placeholder="15000000"
                    />
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        type="button"
                        @click="isModalTerbitkanOpen = false"
                        variant="ghost"
                        size="sm"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :loading="formLisensi.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        Terbitkan Lisensi
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal 4: Perpanjang Masa Aktif Lisensi -->
        <Modal
            :show="isModalRenewOpen"
            @close="isModalRenewOpen = false"
            title="Perpanjang Masa Aktif Lisensi"
            maxWidth="sm"
        >
            <form @submit.prevent="submitRenew" class="space-y-4 text-xs">
                <p class="text-slate-400">
                    Perpanjang masa aktif lisensi untuk
                    <strong class="text-white">{{
                        selectedLicense?.nomor_lisensi
                    }}</strong
                    >.
                </p>

                <div>
                    <label class="mb-1 block font-medium text-slate-300"
                        >Tambahan Durasi (Bulan)</label
                    >
                    <Input
                        v-model="formRenew.perpanjang_bulan"
                        type="number"
                        placeholder="12"
                        required
                    />
                </div>

                <div>
                    <label class="mb-1 block font-medium text-slate-300"
                        >Biaya Perpanjangan Tambahan (Rp)</label
                    >
                    <Input
                        v-model="formRenew.nilai_kontrak_tambahan"
                        type="number"
                        placeholder="6000000"
                    />
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        type="button"
                        @click="isModalRenewOpen = false"
                        variant="ghost"
                        size="sm"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :loading="formRenew.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        Simpan Perpanjangan
                    </Button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
