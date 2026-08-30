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
    ExternalLink,
    Trash2,
} from 'lucide-vue-next';

interface Props {
    kliens: any;
    filters: any;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const tipeSekolah = ref(props.filters.tipe_sekolah || '');
const statusKlien = ref(props.filters.status_klien || '');
const isModalOpen = ref(false);

const form = useForm({
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

const submitKlien = () => {
    form.post('/admin/klien', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Sekolah Mitra (CRM) - AksaraEdu HQ" />

        <template #header-title>
            <h1 class="text-base font-bold tracking-tight text-slate-100">
                Manajemen Sekolah Mitra (CRM)
            </h1>
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

                <Button
                    @click="isModalOpen = true"
                    variant="primary"
                    size="sm"
                    class="bg-emerald-500 hover:bg-emerald-600"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Tambah Sekolah Mitra
                </Button>
            </div>

            <!-- Klien Table / List -->
            <Card class="border-slate-800 bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/60 font-semibold tracking-wider text-slate-400 uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Sekolah / NPSN</th>
                                <th class="px-4 py-3">Jenjang & Lokasi</th>
                                <th class="px-4 py-3">Kontak PIC</th>
                                <th class="px-4 py-3">Lisensi Terkini</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
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
                                <td class="px-4 py-3">
                                    <p class="text-sm font-bold text-white">
                                        {{ klien.nama_sekolah }}
                                    </p>
                                    <p
                                        class="font-mono text-[11px] text-emerald-400"
                                    >
                                        NPSN: {{ klien.npsn }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        variant="outline"
                                        size="sm"
                                        class="mb-1 font-bold uppercase"
                                        >{{ klien.tipe_sekolah }}</Badge
                                    >
                                    <p
                                        class="flex items-center gap-1 text-[11px] text-slate-400"
                                    >
                                        <MapPin
                                            class="h-3 w-3 text-slate-500"
                                        />
                                        {{ klien.kabupaten_kota }},
                                        {{ klien.provinsi }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-200">
                                        {{ klien.nama_pic }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        {{ klien.kontak_pic_wa }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        v-if="
                                            klien.lisensis &&
                                            klien.lisensis.length > 0
                                        "
                                    >
                                        <Badge
                                            :variant="
                                                klien.lisensis[0]
                                                    .model_lisensi ===
                                                'beli_putus'
                                                    ? 'success'
                                                    : 'info'
                                            "
                                        >
                                            {{
                                                klien.lisensis[0]
                                                    .model_lisensi ===
                                                'beli_putus'
                                                    ? 'Beli Putus'
                                                    : 'SaaS Cloud'
                                            }}
                                        </Badge>
                                        <p
                                            class="mt-1 font-mono text-[10px] text-slate-400"
                                        >
                                            {{
                                                klien.lisensis[0].nomor_lisensi
                                            }}
                                        </p>
                                    </div>
                                    <span
                                        v-else
                                        class="text-[11px] text-slate-500 italic"
                                        >Belum diterbitkan</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            klien.status_klien === 'aktif'
                                                ? 'success'
                                                : klien.status_klien ===
                                                    'prospek'
                                                  ? 'warning'
                                                  : 'danger'
                                        "
                                    >
                                        {{ klien.status_klien }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/admin/klien/${klien.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700"
                                    >
                                        Detail
                                        <ChevronRight class="h-3.5 w-3.5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="kliens.data.length === 0">
                                <td
                                    colspan="6"
                                    class="py-8 text-center text-xs text-slate-500"
                                >
                                    Tidak ada data sekolah mitra yang cocok
                                    dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <!-- Modal Tambah Sekolah Mitra Baru -->
        <Modal
            :show="isModalOpen"
            @close="isModalOpen = false"
            title="Tambah Satuan Pendidikan Mitra Baru"
            maxWidth="lg"
        >
            <form @submit.prevent="submitKlien" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >NPSN (8 Digit)</label
                        >
                        <Input
                            v-model="form.npsn"
                            placeholder="20104050"
                            :error="form.errors.npsn"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Jenjang</label
                        >
                        <select
                            v-model="form.tipe_sekolah"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="smk">SMK</option>
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
                        >Nama Satuan Pendidikan</label
                    >
                    <Input
                        v-model="form.nama_sekolah"
                        placeholder="SMK Negeri 1 Aksara Nusantara"
                        :error="form.errors.nama_sekolah"
                        required
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Yayasan Induk / Dinas Pembina</label
                    >
                    <Input
                        v-model="form.yayasan_induk"
                        placeholder="Dinas Pendidikan Provinsi Jawa Barat"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Nama PIC / Penanggung Jawab</label
                        >
                        <Input
                            v-model="form.nama_pic"
                            placeholder="Drs. H. Mulyadi, M.Kom"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >No. WhatsApp PIC</label
                        >
                        <Input
                            v-model="form.kontak_pic_wa"
                            placeholder="081234567890"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Email Resmi</label
                    >
                    <Input
                        v-model="form.email_pic"
                        type="email"
                        placeholder="smkn1@sch.id"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Provinsi</label
                        >
                        <Input
                            v-model="form.provinsi"
                            placeholder="Jawa Barat"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Kabupaten / Kota</label
                        >
                        <Input
                            v-model="form.kabupaten_kota"
                            placeholder="Kota Bandung"
                            required
                        />
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-800 pt-4"
                >
                    <Button
                        @click="isModalOpen = false"
                        variant="ghost"
                        size="sm"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :loading="form.processing"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        Simpan Sekolah
                    </Button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
