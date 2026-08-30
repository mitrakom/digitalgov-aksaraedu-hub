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
    Package,
    Plus,
    ShieldCheck,
    Download,
    Trash2,
    CheckCircle2,
    FileCode2,
    Calendar,
} from 'lucide-vue-next';

interface Props {
    releases: any;
    recentDownloads?: any[];
}

defineProps<Props>();

const isModalOpen = ref(false);

const form = useForm({
    nomor_versi: '1.0.2',
    tipe_rilis: 'patch_bugfix',
    ringkasan_perubahan:
        '• Perbaikan kestabilan koneksi ujian CBT massal\n• Optimasi memori render modul raport Kurikulum Merdeka\n• Peningkatan sanitasi input keamanan',
    minimal_versi_lms: '1.0.0',
    is_public: true,
    is_critical_patch: true,
    checksum_sha256: '',
});

const submitRelease = () => {
    form.post('/admin/rilis', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteRelease = (id: string, version: string) => {
    if (confirm(`Hapus paket rilis v${version} dari registry pusat?`)) {
        router.delete(`/admin/rilis/${id}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head
            title="Repositori Rilis & Pembaruan (OTA Registry) - AksaraEdu HQ"
        />

        <template #header-title>
            <h1 class="text-base font-bold tracking-tight text-slate-100">
                Repositori Rilis & Pembaruan (OTA Registry)
            </h1>
        </template>

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    Kelola paket rilis pembaruan, patch bugfix resmi, dan
                    changelog terdistribusi ke seluruh klien.
                </p>
                <Button
                    @click="isModalOpen = true"
                    variant="primary"
                    size="sm"
                    class="bg-emerald-500 hover:bg-emerald-600"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Publikasikan Rilis Baru
                </Button>
            </div>

            <!-- Release List Grid -->
            <div class="grid grid-cols-1 gap-4">
                <Card
                    v-for="rel in releases.data"
                    :key="rel.id"
                    class="border-slate-800 bg-slate-900 p-6"
                >
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 font-mono font-bold text-emerald-400"
                            >
                                v{{ rel.nomor_versi }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white">
                                        Rilis Versi {{ rel.nomor_versi }}
                                    </h3>
                                    <Badge
                                        :variant="
                                            rel.tipe_rilis === 'patch_bugfix'
                                                ? 'success'
                                                : 'info'
                                        "
                                        size="sm"
                                    >
                                        {{ rel.tipe_rilis }}
                                    </Badge>
                                    <Badge
                                        v-if="rel.is_critical_patch"
                                        variant="warning"
                                        size="sm"
                                        >Critical Security Patch</Badge
                                    >
                                </div>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Kompatibel mulai versi:
                                    <span class="font-mono text-emerald-400"
                                        >v{{ rel.minimal_versi_lms }}</span
                                    >
                                    | Didownload:
                                    {{ rel.riwayat_updates_count || 0 }} kali
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="mr-2 flex items-center gap-1 text-xs text-slate-400"
                            >
                                <Calendar class="h-3.5 w-3.5 text-slate-500" />
                                {{
                                    rel.published_at
                                        ? new Date(
                                              rel.published_at,
                                          ).toLocaleDateString('id-ID')
                                        : '-'
                                }}
                            </span>
                            <a
                                v-if="rel.file_path_zip"
                                :href="`/admin/rilis/${rel.id}/download`"
                                target="_blank"
                                class="inline-flex items-center rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1.5 text-xs font-semibold text-emerald-400 transition-colors hover:bg-emerald-500/20"
                                title="Unduh Berkas ZIP Rilis"
                            >
                                <Download class="mr-1 h-3.5 w-3.5" /> Unduh ZIP
                            </a>
                            <Button
                                @click="deleteRelease(rel.id, rel.nomor_versi)"
                                variant="ghost"
                                size="sm"
                                class="text-rose-400 hover:text-rose-300"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Changelog -->
                    <div class="mt-4">
                        <h4 class="mb-1.5 text-xs font-semibold text-slate-300">
                            Catatan Perubahan (Changelog):
                        </h4>
                        <div
                            class="rounded-xl border border-slate-800/80 bg-slate-950/60 p-3.5 font-mono text-xs leading-relaxed whitespace-pre-line text-slate-300"
                        >
                            {{ rel.ringkasan_perubahan }}
                        </div>
                    </div>

                    <!-- Security Checksum Strip -->
                    <div
                        class="mt-4 flex flex-col items-start justify-between gap-2 border-t border-slate-800/80 pt-3 font-mono text-[11px] text-slate-400 sm:flex-row sm:items-center"
                    >
                        <span class="max-w-xl truncate"
                            >SHA-256:
                            {{
                                rel.checksum_sha256 || 'Generated Automatically'
                            }}</span
                        >
                        <span class="flex items-center gap-1 text-emerald-400"
                            ><ShieldCheck class="h-3.5 w-3.5" /> RSA Signed
                            Integrity</span
                        >
                    </div>
                </Card>

                <div
                    v-if="releases.data.length === 0"
                    class="py-12 text-center text-xs text-slate-500"
                >
                    Belum ada rilis versi software di repository pusat.
                </div>
            </div>

            <!-- Recent Client Update Audit Trail -->
            <Card class="mt-8 border-slate-800 bg-slate-900 p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-800 pb-4"
                >
                    <div>
                        <h3
                            class="flex items-center gap-2 text-sm font-bold text-white"
                        >
                            <CheckCircle2 class="h-4 w-4 text-emerald-400" />
                            Riwayat Log Unduhan Klien (Update Audit Trail)
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Log sinkronisasi dan unduhan paket rilis oleh
                            instans LMS sekolah klien.
                        </p>
                    </div>
                    <Badge variant="info" size="sm"
                        >{{ recentDownloads?.length || 0 }} Log Terakhir</Badge
                    >
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead
                            class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Waktu Unduh</th>
                                <th class="px-4 py-3">Sekolah / Klien</th>
                                <th class="px-4 py-3">NPSN</th>
                                <th class="px-4 py-3">Versi Rilis</th>
                                <th class="px-4 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            <tr
                                v-for="item in recentDownloads"
                                :key="item.id"
                                class="hover:bg-slate-800/40"
                            >
                                <td
                                    class="px-4 py-3 whitespace-nowrap text-slate-400"
                                >
                                    {{
                                        item.downloaded_at
                                            ? new Date(
                                                  item.downloaded_at,
                                              ).toLocaleString('id-ID')
                                            : '-'
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 font-sans font-medium whitespace-nowrap text-white"
                                >
                                    {{
                                        item.lisensi?.klien_sekolah
                                            ?.nama_sekolah ||
                                        'Instans Klien Terlisensi'
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 whitespace-nowrap text-emerald-400"
                                >
                                    {{
                                        item.lisensi?.klien_sekolah?.npsn ||
                                        item.lisensi?.nomor_lisensi ||
                                        '-'
                                    }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Badge variant="success" size="sm"
                                        >v{{
                                            item.rilis_pembaruan?.nomor_versi ||
                                            '-'
                                        }}</Badge
                                    >
                                </td>
                                <td
                                    class="px-4 py-3 whitespace-nowrap text-slate-400"
                                >
                                    {{ item.ip_address || '127.0.0.1' }}
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !recentDownloads ||
                                    recentDownloads.length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-4 py-8 text-center font-sans text-xs text-slate-500"
                                >
                                    Belum ada log unduhan update dari klien.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <!-- Modal Publish Release -->
        <Modal
            :show="isModalOpen"
            @close="isModalOpen = false"
            title="Publikasikan Rilis Pembaruan Baru"
            maxWidth="lg"
        >
            <form @submit.prevent="submitRelease" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Nomor Versi (Semver)</label
                        >
                        <Input
                            v-model="form.nomor_versi"
                            placeholder="1.0.2"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-300"
                            >Tipe Rilis</label
                        >
                        <select
                            v-model="form.tipe_rilis"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-slate-100"
                        >
                            <option value="patch_bugfix">Patch Bugfix</option>
                            <option value="minor_feature">Minor Feature</option>
                            <option value="major_curriculum">
                                Major Curriculum Upgrade
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Minimal Versi LMS Terpasang</label
                    >
                    <Input
                        v-model="form.minimal_versi_lms"
                        placeholder="1.0.0"
                        required
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-300"
                        >Ringkasan Perubahan (Changelog)</label
                    >
                    <textarea
                        v-model="form.ringkasan_perubahan"
                        rows="5"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-xs text-slate-100 placeholder:text-slate-500"
                        placeholder="Tuliskan daftar poin perubahan dan perbaikan..."
                        required
                    ></textarea>
                </div>

                <div
                    class="flex items-center gap-4 pt-1 text-xs text-slate-300"
                >
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="form.is_critical_patch"
                            class="rounded border-slate-700 bg-slate-800 text-emerald-500"
                        />
                        <span>Patch Kritis Darurat</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="form.is_public"
                            class="rounded border-slate-700 bg-slate-800 text-emerald-500"
                        />
                        <span>Tampilkan di Registry Publik</span>
                    </label>
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
                        Publikasikan ke Registry
                    </Button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
