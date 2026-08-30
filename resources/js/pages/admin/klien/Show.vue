<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
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
    ShieldCheck,
} from 'lucide-vue-next';

interface Props {
    klien: any;
}

const props = defineProps<Props>();

const isEditing = ref(false);

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
            `Yakin ingin menghapus sekolah mitra ${props.klien.nama_sekolah}? Seluruh lisensi dan telemetri terkait akan terhapus.`,
        )
    ) {
        router.delete(`/admin/klien/${props.klien.id}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Detail Klien: ${klien.nama_sekolah} - AksaraEdu HQ`" />

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
                            Riwayat Lisensi Resmi
                        </h3>
                    </div>
                    <Link
                        href="/admin/lisensi"
                        class="text-xs text-emerald-400 hover:underline"
                    >
                        + Terbitkan Lisensi Baru
                    </Link>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="lic in klien.lisensis"
                        :key="lic.id"
                        class="border-slate-750 flex flex-col justify-between gap-4 rounded-xl border bg-slate-800/50 p-4 text-xs md:flex-row md:items-center"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-mono text-sm font-bold text-white"
                                    >{{ lic.nomor_lisensi }}</span
                                >
                                <Badge
                                    :variant="
                                        lic.model_lisensi === 'beli_putus'
                                            ? 'success'
                                            : 'info'
                                    "
                                >
                                    {{
                                        lic.model_lisensi === 'beli_putus'
                                            ? 'Beli Putus On-Premise'
                                            : 'Langganan SaaS'
                                    }}
                                </Badge>
                                <Badge
                                    :variant="
                                        lic.status === 'active'
                                            ? 'success'
                                            : 'warning'
                                    "
                                    >{{ lic.status }}</Badge
                                >
                            </div>
                            <p class="text-slate-400">
                                Serial Key:
                                <span class="font-mono text-emerald-400">{{
                                    lic.serial_key || '-'
                                }}</span>
                                | Domain:
                                <span class="text-slate-300">{{
                                    lic.domain_terdaftar || 'Belum di-bind'
                                }}</span>
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Terbit: {{ lic.tanggal_rilis }} | Garansi
                                Bugfix: {{ lic.garansi_bugfix_hingga || '-' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                :href="`/admin/lisensi/${lic.id}/download`"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white hover:bg-emerald-500"
                            >
                                <Download class="h-3.5 w-3.5" /> Unduh .lic
                            </a>
                        </div>
                    </div>
                    <div
                        v-if="!klien.lisensis || klien.lisensis.length === 0"
                        class="py-4 text-center text-xs text-slate-500"
                    >
                        Belum ada lisensi yang diterbitkan untuk sekolah ini.
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
