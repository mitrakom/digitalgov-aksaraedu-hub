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
    ArrowLeft,
    KeyRound,
    Download,
    Activity,
    LifeBuoy,
    Edit,
    Save,
    CheckCircle2,
    Trash2,
    Calendar,
    Package,
    RotateCcw,
    FileCode,
    Plus,
} from 'lucide-vue-next';

interface Props {
    klien: any;
    publicKey?: string;
}

const props = defineProps<Props>();

const isEditing = ref(false);
const isModalTerbitkanOpen = ref(false);
const isModalRenewOpen = ref(false);
const selectedLicense = ref<any>(null);

const form = useForm({
    npsn: props.klien.npsn,
    nama_sekolah: props.klien.nama_sekolah,
    tipe_sekolah: props.klien.tipe_sekolah,
    yayasan_induk: props.klien.yayasan_induk || '',
    nama_pic: props.klien.nama_pic,
    kontak_pic_wa: props.klien.kontak_pic_wa,
    email_pic: props.klien.email_pic,
    provinsi: props.klien.provinsi,
    kabupaten_kota: props.klien.kabupaten_kota,
    alamat_lengkap: props.klien.alamat_lengkap || '',
    status_klien: props.klien.status_klien,
});

const formLisensi = useForm({
    model_lisensi: 'beli_putus',
    tier_paket: 'enterprise',
    domain_terdaftar: '',
    durasi_bulan: 12,
    garansi_bulan: 3,
    nilai_kontrak: 15000000,
    catatan_kontrak: '',
});

const formRenew = useForm({
    perpanjang_bulan: 12,
    nilai_kontrak_tambahan: 6000000,
});

const updateKlien = () => {
    form.put(`/admin/klien/${props.klien.id}`, {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const deleteKlien = () => {
    if (
        confirm(
            `Yakin ingin menghapus data sekolah ${props.klien.nama_sekolah}? Seluruh data lisensi terkait akan terhapus.`,
        )
    ) {
        router.delete(`/admin/klien/${props.klien.id}`);
    }
};

const submitTerbitkanLisensi = () => {
    formLisensi.post(`/admin/klien/${props.klien.id}/lisensi`, {
        onSuccess: () => {
            isModalTerbitkanOpen.value = false;
            formLisensi.reset();
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
            'Reset kaitan hardware fingerprint? Server sekolah dapat melakukan binding ulang pada mesin baru.',
        )
    ) {
        router.post(`/admin/lisensi/${licId}/reset-hardware`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Detail Sekolah: ${klien.nama_sekolah} - AksaraEdu HQ`" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <Link
                    href="/admin/klien"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <h1 class="text-base font-bold tracking-tight text-slate-100">
                    {{ klien.nama_sekolah }}
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <!-- School Info Card -->
            <Card class="border-slate-800 bg-slate-900 p-6">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-800 pb-4 sm:flex-row sm:items-center"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400"
                        >
                            <School class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    size="sm"
                                    class="font-bold uppercase"
                                    >{{ klien.tipe_sekolah }}</Badge
                                >
                                <Badge
                                    :variant="
                                        klien.status_klien === 'aktif'
                                            ? 'success'
                                            : 'warning'
                                    "
                                    >{{ klien.status_klien }}</Badge
                                >
                            </div>
                            <h2 class="mt-1 text-xl font-bold text-white">
                                {{ klien.nama_sekolah }}
                            </h2>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!isEditing"
                            @click="isEditing = true"
                            variant="outline"
                            size="sm"
                        >
                            <Edit class="mr-1 h-3.5 w-3.5" /> Edit Data
                        </Button>
                        <Button
                            v-else
                            @click="updateKlien"
                            :loading="form.processing"
                            variant="primary"
                            size="sm"
                            class="bg-emerald-500 hover:bg-emerald-600"
                        >
                            <Save class="mr-1 h-3.5 w-3.5" /> Simpan Perubahan
                        </Button>
                        <Button @click="deleteKlien" variant="danger" size="sm">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>

                <!-- Details Grid / Edit Form -->
                <div
                    v-if="!isEditing"
                    class="mt-6 grid grid-cols-1 gap-4 text-xs text-slate-300 sm:grid-cols-2 md:grid-cols-4"
                >
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3.5"
                    >
                        <p class="mb-1 text-slate-500">NPSN</p>
                        <p class="font-mono text-sm font-bold text-white">
                            {{ klien.npsn }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3.5"
                    >
                        <p class="mb-1 text-slate-500">
                            PIC / Penanggung Jawab
                        </p>
                        <p class="text-sm font-bold text-white">
                            {{ klien.nama_pic }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3.5"
                    >
                        <p class="mb-1 text-slate-500">WhatsApp & Email</p>
                        <p class="font-semibold text-emerald-400">
                            {{ klien.kontak_pic_wa }}
                        </p>
                        <p class="truncate text-[11px] text-slate-400">
                            {{ klien.email_pic }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3.5"
                    >
                        <p class="mb-1 text-slate-500">Lokasi Wilayah</p>
                        <p class="font-bold text-white">
                            {{ klien.kabupaten_kota }}
                        </p>
                        <p class="text-[11px] text-slate-400">
                            {{ klien.provinsi }}
                        </p>
                    </div>
                </div>

                <form
                    v-else
                    @submit.prevent="updateKlien"
                    class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3"
                >
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >Nama Sekolah</label
                        >
                        <Input v-model="form.nama_sekolah" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >NPSN</label
                        >
                        <Input v-model="form.npsn" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >PIC</label
                        >
                        <Input v-model="form.nama_pic" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >WhatsApp</label
                        >
                        <Input v-model="form.kontak_pic_wa" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >Email</label
                        >
                        <Input v-model="form.email_pic" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400"
                            >Kota</label
                        >
                        <Input v-model="form.kabupaten_kota" />
                    </div>
                </form>
            </Card>

            <!-- Licenses Issued Card -->
            <Card class="border-slate-800 bg-slate-900 p-6">
                <div
                    class="mb-4 flex items-center justify-between border-b border-slate-800 pb-4"
                >
                    <div class="flex items-center gap-2">
                        <KeyRound class="h-4 w-4 text-emerald-400" />
                        <h3 class="text-sm font-bold text-white">
                            Lisensi Resmi Sekolah
                        </h3>
                    </div>
                    <Button
                        @click="isModalTerbitkanOpen = true"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        <Plus class="mr-1 h-3.5 w-3.5" /> Terbitkan Lisensi Baru
                    </Button>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="lic in klien.lisensis"
                        :key="lic.id"
                        class="flex flex-col justify-between gap-4 rounded-xl border border-slate-800 bg-slate-950/60 p-4 text-xs lg:flex-row lg:items-center"
                    >
                        <div class="flex-1 space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-mono text-sm font-bold text-white"
                                >
                                    {{ lic.nomor_lisensi }}
                                </span>
                                <Badge
                                    :variant="
                                        lic.model_lisensi === 'beli_putus'
                                            ? 'success'
                                            : 'info'
                                    "
                                >
                                    {{
                                        lic.model_lisensi === 'beli_putus'
                                            ? 'Beli Putus (On-Premise)'
                                            : 'Langganan (SaaS)'
                                    }}
                                </Badge>
                                <Badge
                                    :variant="
                                        lic.status === 'active'
                                            ? 'success'
                                            : 'warning'
                                    "
                                >
                                    {{ lic.status }}
                                </Badge>
                            </div>
                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-400"
                            >
                                <span>
                                    Serial Key:
                                    <strong
                                        class="font-mono text-emerald-400"
                                        >{{ lic.serial_key || '-' }}</strong
                                    >
                                </span>
                                <span>•</span>
                                <span>
                                    Domain Terdaftar:
                                    <strong class="text-slate-300">{{
                                        lic.domain_terdaftar || 'Semua Domain'
                                    }}</strong>
                                </span>
                                <span>•</span>
                                <span>
                                    Hardware Binding:
                                    <strong class="text-slate-300">{{
                                        lic.hardware_fingerprint
                                            ? 'Terkunci'
                                            : 'Belum Terikat'
                                    }}</strong>
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500">
                                Diterbitkan: {{ lic.tanggal_rilis }}
                                <span v-if="lic.tanggal_kadaluarsa">
                                    | Kadaluarsa:
                                    {{ lic.tanggal_kadaluarsa }}</span
                                >
                                <span v-if="lic.garansi_bugfix_hingga">
                                    | Garansi Bugfix:
                                    {{ lic.garansi_bugfix_hingga }}</span
                                >
                            </p>
                        </div>

                        <!-- Actions for License -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Unduh File Lisensi .lic -->
                            <a
                                :href="`/admin/lisensi/${lic.id}/download`"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white hover:bg-emerald-500"
                                title="Unduh berkas lisensi resmi aksaraedu.lic"
                            >
                                <Download class="h-3.5 w-3.5" /> Unduh .lic
                            </a>

                            <!-- Unduh Bundle Siap Pasang .zip -->
                            <a
                                :href="`/admin/lisensi/${lic.id}/download-bundle`"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-3 py-1.5 font-semibold text-indigo-300 hover:bg-indigo-500/20"
                                title="Unduh paket aplikasi LMS siap pasang untuk sekolah ini"
                            >
                                <Package class="h-3.5 w-3.5" /> Unduh Bundle ZIP
                            </a>

                            <!-- Unduh Web Loader script -->
                            <a
                                :href="`/admin/lisensi/${lic.id}/download-loader`"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-700"
                                title="Unduh aksara-loader.php untuk deployment web hosting otomatis"
                            >
                                <FileCode class="h-3.5 w-3.5 text-slate-400" />
                                Loader
                            </a>

                            <!-- Perpanjang -->
                            <button
                                v-if="lic.model_lisensi === 'langganan'"
                                @click="openRenewModal(lic)"
                                class="rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-700"
                            >
                                Perpanjang
                            </button>

                            <!-- Reset Hardware -->
                            <button
                                v-if="lic.hardware_fingerprint"
                                @click="resetHardware(lic.id)"
                                class="rounded-lg border border-slate-700 bg-slate-800 px-2 py-1.5 text-xs font-semibold text-slate-400 hover:text-amber-400"
                                title="Reset pengikatan Hardware Fingerprint"
                            >
                                <RotateCcw class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="!klien.lisensis || klien.lisensis.length === 0"
                        class="py-6 text-center text-xs text-slate-500"
                    >
                        Belum ada lisensi yang diterbitkan untuk sekolah ini.
                        Klik <strong>+ Terbitkan Lisensi Baru</strong> di atas.
                    </div>
                </div>
            </Card>
        </div>

        <!-- Modal Terbitkan Lisensi Baru -->
        <Modal
            :show="isModalTerbitkanOpen"
            @close="isModalTerbitkanOpen = false"
            title="Terbitkan Lisensi Baru"
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
                        class="bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        Terbitkan Lisensi
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Modal Perpanjang Masa Aktif Lisensi -->
        <Modal
            :show="isModalRenewOpen"
            @close="isModalRenewOpen = false"
            title="Perpanjang Masa Aktif Lisensi"
            maxWidth="sm"
        >
            <form @submit.prevent="submitRenew" class="space-y-4 text-xs">
                <p class="text-slate-400">
                    Perpanjang lisensi
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
                        >Biaya Perpanjangan (Rp)</label
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
