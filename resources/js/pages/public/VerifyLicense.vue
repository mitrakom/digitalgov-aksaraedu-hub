<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/PublicLayout.vue';
import Button from '../../components/ui/Button.vue';
import Badge from '../../components/ui/Badge.vue';
import Card from '../../components/ui/Card.vue';
import Input from '../../components/ui/Input.vue';
import {
    ShieldCheck,
    Search,
    CheckCircle2,
    AlertTriangle,
    School,
    MapPin,
    Calendar,
    KeyRound,
    ShieldAlert,
} from 'lucide-vue-next';

const npsnInput = ref('');
const isLoading = ref(false);
const searchResult = ref<any>(null);
const searchError = ref<string | null>(null);

const verifyNpsn = async () => {
    if (!npsnInput.value.trim()) return;

    isLoading.value = true;
    searchResult.value = null;
    searchError.value = null;

    try {
        const res = await fetch(
            `/api/v1/license/verify/${encodeURIComponent(npsnInput.value.trim())}`,
        );
        const data = await res.json();

        if (res.ok && data.verified) {
            searchResult.value = data;
        } else {
            searchError.value =
                data.message ||
                'NPSN sekolah tidak ditemukan dalam direktori lisensi resmi AksaraEdu.';
        }
    } catch (err) {
        searchError.value =
            'Gagal menghubungi server verifikasi. Silakan coba lagi.';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <PublicLayout>
        <Head
            title="Verifikasi Keaslian Lisensi NPSN - AksaraEdu Central Hub"
        />

        <div class="py-16">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <Badge variant="info" size="md" class="mb-3"
                    >Portal Resmi Verifikasi Nasional</Badge
                >
                <h1
                    class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl"
                >
                    Cek Keaslian Lisensi Resmi AksaraEdu
                </h1>
                <p
                    class="mx-auto mt-3 max-w-2xl text-sm text-slate-300 sm:text-base"
                >
                    Pastikan instans LMS & CBT di sekolah Anda memiliki lisensi
                    legal bertanda tangan kriptografis RSA-4096 resmi dari
                    AksaraEdu Central Hub.
                </p>

                <!-- NPSN Search Bar Form -->
                <div class="mx-auto mt-10 max-w-xl">
                    <form
                        @submit.prevent="verifyNpsn"
                        class="flex flex-col gap-2.5 sm:flex-row"
                    >
                        <div class="relative flex-1">
                            <Input
                                v-model="npsnInput"
                                placeholder="Masukkan 8 Digit NPSN Sekolah (Contoh: 20104050)"
                                class="rounded-xl border-slate-700 bg-slate-900 px-4 py-3.5 text-base text-white focus:border-emerald-500"
                            />
                        </div>
                        <Button
                            type="submit"
                            :loading="isLoading"
                            variant="primary"
                            size="lg"
                            class="justify-center rounded-xl bg-emerald-500 font-bold hover:bg-emerald-600"
                        >
                            <Search class="mr-2 h-4 w-4" />
                            Verifikasi
                        </Button>
                    </form>
                    <p class="mt-2 text-center text-[11px] text-slate-500">
                        Pengecekan langsung terhubung ke master cryptography
                        licensing repository.
                    </p>
                </div>

                <!-- Result Section: Verified Card -->
                <div
                    v-if="searchResult"
                    class="animate-in zoom-in-95 mt-12 text-left"
                >
                    <Card
                        class="relative overflow-hidden border-emerald-500/50 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 p-6 shadow-2xl sm:p-8"
                    >
                        <!-- Glow Ribbon -->
                        <div
                            class="flex flex-col justify-between gap-4 border-b border-slate-800 pb-6 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-500/40 bg-emerald-500/20 text-emerald-400"
                                >
                                    <ShieldCheck class="h-7 w-7" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold tracking-wider text-emerald-400 uppercase"
                                            >Lisensi Terverifikasi Sah</span
                                        >
                                        <Badge variant="success"
                                            >Resmi Aktif</Badge
                                        >
                                    </div>
                                    <h3
                                        class="mt-0.5 text-xl font-bold text-white"
                                    >
                                        {{ searchResult.nama_sekolah }}
                                    </h3>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400">
                                    Nomor Pokok Sekolah Nasional
                                </p>
                                <p
                                    class="font-mono text-lg font-bold tracking-widest text-white"
                                >
                                    {{ searchResult.npsn }}
                                </p>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div
                            class="my-6 grid grid-cols-1 gap-6 text-xs text-slate-300 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <div
                                class="rounded-xl border border-slate-800 bg-slate-800/50 p-3.5"
                            >
                                <p
                                    class="mb-1 flex items-center gap-1.5 text-slate-400"
                                >
                                    <School
                                        class="h-3.5 w-3.5 text-emerald-400"
                                    />
                                    Jenjang / Tipe
                                </p>
                                <p
                                    class="text-sm font-bold text-white uppercase"
                                >
                                    {{ searchResult.tipe_sekolah }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-800 bg-slate-800/50 p-3.5"
                            >
                                <p
                                    class="mb-1 flex items-center gap-1.5 text-slate-400"
                                >
                                    <MapPin class="h-3.5 w-3.5 text-teal-400" />
                                    Wilayah / Kota
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ searchResult.kabupaten_kota }},
                                    {{ searchResult.provinsi }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-800 bg-slate-800/50 p-3.5"
                            >
                                <p
                                    class="mb-1 flex items-center gap-1.5 text-slate-400"
                                >
                                    <KeyRound
                                        class="h-3.5 w-3.5 text-sky-400"
                                    />
                                    Model Lisensi
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ searchResult.model_lisensi }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-800 bg-slate-800/50 p-3.5"
                            >
                                <p
                                    class="mb-1 flex items-center gap-1.5 text-slate-400"
                                >
                                    <Calendar
                                        class="h-3.5 w-3.5 text-amber-400"
                                    />
                                    Tahun Penerbitan
                                </p>
                                <p class="text-sm font-bold text-white">
                                    Tahun {{ searchResult.tahun_terbit }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-800 bg-slate-800/50 p-3.5 sm:col-span-2"
                            >
                                <p
                                    class="mb-1 flex items-center gap-1.5 text-slate-400"
                                >
                                    <ShieldCheck
                                        class="h-3.5 w-3.5 text-emerald-400"
                                    />
                                    Status Garansi Bugfix Resmi
                                </p>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <Badge
                                        :variant="
                                            searchResult.garansi_aktif
                                                ? 'success'
                                                : 'warning'
                                        "
                                    >
                                        {{
                                            searchResult.garansi_aktif
                                                ? 'Garansi Aktif'
                                                : 'Masa Garansi Berakhir'
                                        }}
                                    </Badge>
                                    <span
                                        v-if="searchResult.garansi_hingga"
                                        class="text-slate-300"
                                        >Hingga
                                        {{ searchResult.garansi_hingga }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-950/30 p-4 text-xs text-emerald-200"
                        >
                            <ShieldCheck
                                class="h-5 w-5 shrink-0 text-emerald-400"
                            />
                            <span
                                >Instans ini terproteksi oleh lisensi resmi
                                vendor AksaraEdu dengan enkripsi RSA-4096. Hak
                                kekayaan intelektual terlindungi.</span
                            >
                        </div>
                    </Card>
                </div>

                <!-- Result Section: Error Card -->
                <div
                    v-else-if="searchError"
                    class="animate-in zoom-in-95 mt-12 text-left"
                >
                    <Card
                        class="border-rose-500/50 bg-slate-900 p-6 text-center sm:p-8"
                    >
                        <div
                            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full border border-rose-500/40 bg-rose-500/20 text-rose-400"
                        >
                            <ShieldAlert class="h-6 w-6" />
                        </div>
                        <h3 class="mb-1 text-lg font-bold text-white">
                            NPSN Tidak Terdaftar Resmi
                        </h3>
                        <p
                            class="mx-auto mb-6 max-w-md text-xs leading-relaxed text-rose-300"
                        >
                            {{ searchError }}
                        </p>
                        <Button
                            as="link"
                            href="/pricing"
                            variant="outline"
                            size="sm"
                            class="border-slate-700 text-slate-300 hover:text-white"
                        >
                            Ajukan Penerbitan Lisensi Baru
                        </Button>
                    </Card>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
