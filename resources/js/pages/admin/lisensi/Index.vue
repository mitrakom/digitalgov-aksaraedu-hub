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
    KeyRound,
    Plus,
    Search,
    Download,
    Package,
    RotateCcw,
    ShieldAlert,
    ShieldCheck,
    Calendar,
    Copy,
    ExternalLink,
    Cpu,
} from 'lucide-vue-next';

interface Props {
    lisensis: any;
    kliens: any[];
    filters: any;
    publicKey: string;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const modelFilter = ref(props.filters.model_lisensi || '');
const statusFilter = ref(props.filters.status || '');

const isCreateModalOpen = ref(false);
const isRenewModalOpen = ref(false);
const isKeyModalOpen = ref(false);
const selectedLicense = ref<any>(null);
const copyKeySuccess = ref(false);

const createForm = useForm({
    klien_sekolah_id: props.kliens.length > 0 ? props.kliens[0].id : '',
    model_lisensi: 'beli_putus',
    tier_paket: 'enterprise',
    domain_terdaftar: '',
    tanggal_rilis: new Date().toISOString().split('T')[0],
    durasi_bulan: 12,
    garansi_bulan: 3,
    nilai_kontrak: 15000000,
    catatan_kontrak: '',
});

const renewForm = useForm({
    perpanjang_bulan: 12,
    nilai_kontrak_tambahan: 6000000,
});

const handleFilter = () => {
    router.get(
        '/admin/lisensi',
        {
            search: search.value,
            model_lisensi: modelFilter.value,
            status: statusFilter.value,
        },
        { preserveState: true, replace: true },
    );
};

const submitCreateLicense = () => {
    createForm.post('/admin/lisensi', {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
    });
};

const openRenewModal = (lic: any) => {
    selectedLicense.value = lic;
    isRenewModalOpen.value = true;
};

const submitRenew = () => {
    if (!selectedLicense.value) return;
    renewForm.post(`/admin/lisensi/${selectedLicense.value.id}/renew`, {
        onSuccess: () => {
            isRenewModalOpen.value = false;
        },
    });
};

const resetHardware = (id: string) => {
    if (
        confirm(
            'Reset kaitan hardware fingerprint? Server sekolah dapat melakukan binding ulang ke perangkat baru.',
        )
    ) {
        router.post(`/admin/lisensi/${id}/reset-hardware`);
    }
};

const revokeLicense = (id: string) => {
    if (
        confirm(
            'Cabut lisensi ini? Klien tidak dapat lagi melakukan sinkronisasi dan status akan dibatalkan.',
        )
    ) {
        router.post(`/admin/lisensi/${id}/revoke`);
    }
};

const copyPublicKey = () => {
    navigator.clipboard.writeText(props.publicKey);
    copyKeySuccess.value = true;
    setTimeout(() => {
        copyKeySuccess.value = false;
    }, 2000);
};
</script>

<template>
    <AdminLayout>
        <Head title="Master Lisensi & Kriptografi RSA - AksaraEdu HQ" />

        <template #header-title>
            <h1 class="text-base font-bold tracking-tight text-slate-100">
                Master Lisensi & Kriptografi RSA
            </h1>
        </template>

        <div class="space-y-6">
            <!-- Top Action Bar -->
            <div
                class="flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div class="flex flex-1 flex-col gap-2.5 sm:flex-row">
                    <div class="relative flex-1">
                        <Input
                            v-model="search"
                            placeholder="Cari nomor lisensi, serial key, domain, NPSN..."
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <select
                        v-model="modelFilter"
                        @change="handleFilter"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200"
                    >
                        <option value="">Semua Model</option>
                        <option value="beli_putus">Beli Putus</option>
                        <option value="langganan">Langganan SaaS</option>
                    </select>
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="grace_period">Grace Period</option>
                        <option value="expired">Expired</option>
                        <option value="revoked">Revoked</option>
                    </select>
                    <Button @click="handleFilter" variant="secondary" size="sm">
                        <Search class="mr-1 h-3.5 w-3.5" /> Filter
                    </Button>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        @click="isKeyModalOpen = true"
                        variant="outline"
                        size="sm"
                    >
                        <KeyRound class="mr-1.5 h-4 w-4 text-emerald-400" /> RSA
                        Public Key
                    </Button>
                    <Button
                        @click="isCreateModalOpen = true"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        <Plus class="mr-1.5 h-4 w-4" /> Terbitkan Lisensi Baru
                    </Button>
                </div>
            </div>

            <!-- License Table -->
            <Card class="border-slate-800 bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/60 font-semibold tracking-wider text-slate-400 uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">
                                    Nomor Lisensi / Sekolah
                                </th>
                                <th class="px-4 py-3">Serial Key & Token</th>
                                <th class="px-4 py-3">Model & Tier</th>
                                <th class="px-4 py-3">Node & Hardware</th>
                                <th class="px-4 py-3">Masa Aktif & Garansi</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-800/60 text-slate-300"
                        >
                            <tr
                                v-for="lic in lisensis.data"
                                :key="lic.id"
                                class="transition-colors hover:bg-slate-800/40"
                            >
                                <td class="px-4 py-3">
                                    <p
                                        class="font-mono text-xs font-bold text-white"
                                    >
                                        {{ lic.nomor_lisensi }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] font-semibold text-emerald-400"
                                    >
                                        {{ lic.klien_sekolah?.nama_sekolah }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NPSN: {{ lic.klien_sekolah?.npsn }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="space-y-1 font-mono text-[11px]"
                                    >
                                        <p
                                            class="inline-block rounded border border-slate-700 bg-slate-800 px-2 py-0.5 text-white"
                                        >
                                            {{ lic.serial_key }}
                                        </p>
                                        <p
                                            class="max-w-[140px] truncate text-[10px] text-slate-400"
                                        >
                                            {{ lic.token_api }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            lic.model_lisensi === 'beli_putus'
                                                ? 'success'
                                                : 'info'
                                        "
                                        size="sm"
                                        class="mb-1"
                                    >
                                        {{
                                            lic.model_lisensi === 'beli_putus'
                                                ? 'Beli Putus'
                                                : 'SaaS Cloud'
                                        }}
                                    </Badge>
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        {{ lic.tier_paket }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-200">
                                        {{
                                            lic.domain_terdaftar ||
                                            'Belum di-bind'
                                        }}
                                    </p>
                                    <div
                                        class="mt-0.5 flex items-center gap-1 font-mono text-[10px] text-slate-500"
                                    >
                                        <Cpu class="h-3 w-3" />
                                        <span>{{
                                            lic.hardware_fingerprint
                                                ? 'Terkunci (Node Active)'
                                                : 'Bebas / Siap Bind'
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-[11px] text-slate-300">
                                        {{
                                            lic.tanggal_kadaluarsa
                                                ? `Hingga ${lic.tanggal_kadaluarsa}`
                                                : 'Lifetime (Selamanya)'
                                        }}
                                    </p>
                                    <p class="text-[10px] text-emerald-400">
                                        Garansi:
                                        {{ lic.garansi_bugfix_hingga || '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            lic.status === 'active'
                                                ? 'success'
                                                : lic.status === 'grace_period'
                                                  ? 'warning'
                                                  : 'danger'
                                        "
                                    >
                                        {{ lic.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <a
                                            :href="`/admin/lisensi/${lic.id}/download-bundle`"
                                            title="Unduh Paket Bundle Siap Pasang Sekolah (.zip)"
                                            class="rounded-lg border border-indigo-500/30 bg-indigo-600/20 p-1.5 text-indigo-300 transition-colors hover:bg-indigo-600 hover:text-white"
                                        >
                                            <Package class="h-3.5 w-3.5" />
                                        </a>
                                        <a
                                            :href="`/admin/lisensi/${lic.id}/download`"
                                            title="Unduh file lisensi aksaraedu.lic"
                                            class="rounded-lg border border-emerald-500/30 bg-emerald-600/20 p-1.5 text-emerald-300 transition-colors hover:bg-emerald-600 hover:text-white"
                                        >
                                            <Download class="h-3.5 w-3.5" />
                                        </a>
                                        <button
                                            v-if="
                                                lic.model_lisensi ===
                                                'langganan'
                                            "
                                            @click="openRenewModal(lic)"
                                            title="Perpanjang Kontrak Langganan"
                                            class="cursor-pointer rounded-lg border border-teal-500/30 bg-teal-600/20 p-1.5 text-teal-300 transition-colors hover:bg-teal-600 hover:text-white"
                                        >
                                            <Calendar class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            @click="resetHardware(lic.id)"
                                            title="Reset Hardware Node Fingerprint"
                                            class="cursor-pointer rounded-lg bg-slate-800 p-1.5 text-slate-300 transition-colors hover:bg-slate-700"
                                        >
                                            <RotateCcw class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            v-if="lic.status !== 'revoked'"
                                            @click="revokeLicense(lic.id)"
                                            title="Cabut Lisensi (Revoke)"
                                            class="cursor-pointer rounded-lg border border-rose-500/30 bg-rose-600/20 p-1.5 text-rose-300 transition-colors hover:bg-rose-600 hover:text-white"
                                        >
                                            <ShieldAlert class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="lisensis.data.length === 0">
                                <td
                                    colspan="7"
                                    class="py-8 text-center text-xs text-slate-500"
                                >
                                    Belum ada lisensi yang cocok dengan kriteria
                                    pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <!-- Modal Terbitkan Lisensi Baru -->
        <Modal
            :show="isCreateModalOpen"
            @close="isCreateModalOpen = false"
            title="Terbitkan Lisensi Resmi AksaraEdu"
            maxWidth="lg"
        >
            <form @submit.prevent="submitCreateLicense" class="space-y-4">
                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Pilih Satuan Pendidikan Mitra</label
                    >
                    <select
                        v-model="createForm.klien_sekolah_id"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        required
                    >
                        <option v-for="k in kliens" :key="k.id" :value="k.id">
                            {{ k.nama_sekolah }} (NPSN: {{ k.npsn }} -
                            {{ k.tipe_sekolah.toUpperCase() }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Model Lisensi</label
                        >
                        <select
                            v-model="createForm.model_lisensi"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="beli_putus">
                                Beli Putus On-Premise
                            </option>
                            <option value="langganan">
                                Langganan Cloud SaaS
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Tier Paket</label
                        >
                        <select
                            v-model="createForm.tier_paket"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="standar">
                                Standar (LMS + CBT Engine)
                            </option>
                            <option value="enterprise">
                                Enterprise (Lengkap + Rapor)
                            </option>
                            <option value="lite">Lite</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Domain Terdaftar (Opsional)</label
                    >
                    <Input
                        v-model="createForm.domain_terdaftar"
                        placeholder="lms.smkn1aksara.sch.id"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Tanggal Mulai Berlaku</label
                        >
                        <Input
                            v-model="createForm.tanggal_rilis"
                            type="date"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                        >
                            {{
                                createForm.model_lisensi === 'langganan'
                                    ? 'Durasi Masa Aktif (Bulan)'
                                    : 'Masa Garansi Bugfix (Bulan)'
                            }}
                        </label>
                        <Input
                            v-if="createForm.model_lisensi === 'langganan'"
                            v-model.number="createForm.durasi_bulan"
                            type="number"
                        />
                        <Input
                            v-else
                            v-model.number="createForm.garansi_bulan"
                            type="number"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Nilai Kontrak Pengadaan (IDR)</label
                    >
                    <Input
                        v-model.number="createForm.nilai_kontrak"
                        type="number"
                        required
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Catatan Kontrak</label
                    >
                    <Input
                        v-model="createForm.catatan_kontrak"
                        placeholder="Nomor SPK / Keterangan Pembelian BOS"
                    />
                </div>

                <div
                    class="rounded-lg border border-emerald-500/20 bg-emerald-950/40 p-3 text-xs text-emerald-300"
                >
                    <ShieldCheck class="mr-1 inline h-4 w-4 text-emerald-400" />
                    Sistem akan menandatangani payload menggunakan
                    <strong>RSA-4096 Private Key</strong> dan menghasilkan
                    serial key otomatis.
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        @click="isCreateModalOpen = false"
                        variant="ghost"
                        size="sm"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :loading="createForm.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        Terbitkan & Tandatangani Lisensi
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal Perpanjang Masa Aktif -->
        <Modal
            :show="isRenewModalOpen"
            @close="isRenewModalOpen = false"
            title="Perpanjang Kontrak Langganan"
            maxWidth="md"
        >
            <form @submit.prevent="submitRenew" class="space-y-4">
                <p class="text-xs text-slate-300">
                    Perpanjangan lisensi untuk:
                    <strong>{{
                        selectedLicense?.klien_sekolah?.nama_sekolah
                    }}</strong>
                </p>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Perpanjang Durasi (Bulan)</label
                    >
                    <Input
                        v-model.number="renewForm.perpanjang_bulan"
                        type="number"
                        min="1"
                        required
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Nilai Kontrak Tambahan (IDR)</label
                    >
                    <Input
                        v-model.number="renewForm.nilai_kontrak_tambahan"
                        type="number"
                        min="0"
                    />
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        @click="isRenewModalOpen = false"
                        variant="ghost"
                        size="sm"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :loading="renewForm.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        Simpan Perpanjangan
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal RSA Public Key -->
        <Modal
            :show="isKeyModalOpen"
            @close="isKeyModalOpen = false"
            title="Vendor RSA-4096 Public Key"
            maxWidth="lg"
        >
            <div class="space-y-3">
                <p class="text-xs text-slate-300">
                    Kunci publik ini ditanam pada file config core LMS Klien
                    untuk memverifikasi keabsahan tanda tangan lisensi offline
                    tanpa internet.
                </p>
                <textarea
                    :value="publicKey"
                    readonly
                    rows="8"
                    class="w-full rounded-lg border border-slate-800 bg-slate-950 p-3 font-mono text-[10px] text-emerald-400 select-all"
                ></textarea>
                <div class="flex justify-end gap-2 pt-2">
                    <Button
                        @click="copyPublicKey"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        <Copy class="mr-1 h-3.5 w-3.5" />
                        {{ copyKeySuccess ? 'Tersalin!' : 'Salin Public Key' }}
                    </Button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
