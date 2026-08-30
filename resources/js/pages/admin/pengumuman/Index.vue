<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
import Modal from '../../../components/ui/Modal.vue';
import {
    Radio,
    Plus,
    Trash2,
    Bell,
    Power,
    Calendar,
    AlertTriangle,
    Info,
} from 'lucide-vue-next';

interface Props {
    pengumumans: any;
}

defineProps<Props>();

const isModalOpen = ref(false);

const form = useForm({
    judul: '',
    pesan: '',
    tipe: 'info',
    target_model: 'semua',
    is_active: true,
});

const submitBroadcast = () => {
    form.post('/admin/pengumuman', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const toggleStatus = (id: string) => {
    router.patch(`/admin/pengumuman/${id}/toggle`);
};

const deleteAnnouncement = (id: string) => {
    if (confirm('Hapus siaran pengumuman remote ini?')) {
        router.delete(`/admin/pengumuman/${id}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Siaran Remote & Notifikasi Klien - AksaraEdu HQ" />

        <template #header-title>
            <h1 class="text-base font-bold tracking-tight text-slate-100">
                Siaran Remote & Notifikasi Klien
            </h1>
        </template>

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    Kirimkan banner pengumuman pemeliharaan atau info rilis dari
                    Central Hub ke Dashboard Admin LMS sekolah mitra.
                </p>
                <Button
                    @click="isModalOpen = true"
                    variant="primary"
                    size="sm"
                    class="bg-emerald-500 hover:bg-emerald-600"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Buat Siaran Baru
                </Button>
            </div>

            <!-- Broadcast Cards List -->
            <div class="grid grid-cols-1 gap-4">
                <Card
                    v-for="p in pengumumans.data"
                    :key="p.id"
                    class="border-slate-800 bg-slate-900 p-5"
                >
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-slate-800 pb-3 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl font-bold"
                                :class="
                                    p.tipe === 'urgent'
                                        ? 'border border-rose-500/30 bg-rose-500/20 text-rose-400'
                                        : p.tipe === 'warning'
                                          ? 'border border-amber-500/30 bg-amber-500/20 text-amber-400'
                                          : 'border border-sky-500/30 bg-sky-500/20 text-sky-400'
                                "
                            >
                                <Bell class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-white">
                                        {{ p.judul }}
                                    </h3>
                                    <Badge
                                        :variant="
                                            p.tipe === 'urgent'
                                                ? 'danger'
                                                : p.tipe === 'warning'
                                                  ? 'warning'
                                                  : 'info'
                                        "
                                        size="sm"
                                    >
                                        {{ p.tipe }}
                                    </Badge>
                                    <Badge variant="outline" size="sm"
                                        >Target: {{ p.target_model }}</Badge
                                    >
                                </div>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Diterbitkan:
                                    {{
                                        new Date(
                                            p.created_at,
                                        ).toLocaleDateString('id-ID')
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                @click="toggleStatus(p.id)"
                                class="cursor-pointer rounded-lg border px-2.5 py-1 text-xs font-semibold transition-colors"
                                :class="
                                    p.is_active
                                        ? 'border-emerald-500/40 bg-emerald-950/60 text-emerald-300'
                                        : 'border-slate-700 bg-slate-800 text-slate-400'
                                "
                            >
                                {{ p.is_active ? 'Siaran Aktif' : 'Nonaktif' }}
                            </button>
                            <Button
                                @click="deleteAnnouncement(p.id)"
                                variant="ghost"
                                size="sm"
                                class="text-rose-400 hover:text-rose-300"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-slate-300">
                        <p>{{ p.pesan }}</p>
                    </div>
                </Card>

                <div
                    v-if="pengumumans.data.length === 0"
                    class="py-12 text-center text-xs text-slate-500"
                >
                    Belum ada siaran pengumuman remote yang dibuat.
                </div>
            </div>
        </div>

        <!-- Modal Create Broadcast -->
        <Modal
            :show="isModalOpen"
            @close="isModalOpen = false"
            title="Buat Siaran Pengumuman Remote"
            maxWidth="md"
        >
            <form @submit.prevent="submitBroadcast" class="space-y-4">
                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Judul Pengumuman</label
                    >
                    <Input
                        v-model="form.judul"
                        placeholder="Contoh: Rilis Pembaruan Patch v1.0.2 Tersedia"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Tipe / Urgensi</label
                        >
                        <select
                            v-model="form.tipe"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="info">Info Standar</option>
                            <option value="warning">
                                Peringatan / Maintenance
                            </option>
                            <option value="urgent">Kritis / Darurat</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Target Model Klien</label
                        >
                        <select
                            v-model="form.target_model"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="semua">Semua Instans</option>
                            <option value="beli_putus">Beli Putus Saja</option>
                            <option value="langganan">
                                Langganan SaaS Saja
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Isi Pesan Siaran</label
                    >
                    <textarea
                        v-model="form.pesan"
                        rows="4"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-xs text-slate-100 placeholder:text-slate-500"
                        placeholder="Tuliskan pesan lengkap yang akan tampil di dashboard sekolah..."
                        required
                    ></textarea>
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
                        class="bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        Publikasikan Siaran
                    </Button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
