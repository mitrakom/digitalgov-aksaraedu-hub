<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '../../layouts/PublicLayout.vue';
import Button from '../../components/ui/Button.vue';
import Badge from '../../components/ui/Badge.vue';
import Card from '../../components/ui/Card.vue';
import Input from '../../components/ui/Input.vue';
import {
    PlayCircle,
    Zap,
    CheckCircle2,
    ExternalLink,
    Copy,
    Clock,
    Key,
    UserCheck,
    Sparkles,
} from 'lucide-vue-next';

const form = ref({
    nama_pemohon: '',
    nama_sekolah: '',
    tipe_sekolah: 'smk',
    nomor_wa: '',
    email: '',
    estimasi_siswa: 600,
    model_minat: 'beli_putus',
});

const isSubmitting = ref(false);
const demoResult = ref<any>(null);
const copySuccess = ref(false);
const errorMessage = ref<string | null>(null);

const submitDemo = async () => {
    if (
        !form.value.nama_pemohon ||
        !form.value.nama_sekolah ||
        !form.value.nomor_wa ||
        !form.value.email
    ) {
        errorMessage.value = 'Harap lengkapi semua kolom formulir demo.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        const res = await fetch('/api/v1/leads/demo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        const data = await res.json();

        if (res.ok && data.status === 'success') {
            demoResult.value = data.data;
        } else {
            errorMessage.value =
                data.message || 'Gagal menyiapkan demo sandbox.';
        }
    } catch (err) {
        errorMessage.value = 'Terjadi kesalahan jaringan saat membuat demo.';
    } finally {
        isSubmitting.value = false;
    }
};

const copyCredentials = () => {
    if (!demoResult.value) return;
    const text = `Akses Demo AksaraEdu:\nURL: ${demoResult.value.demo_url}\nUsername: ${demoResult.value.username_demo}\nPassword: ${demoResult.value.password_demo}`;
    navigator.clipboard.writeText(text);
    copySuccess.value = true;
    setTimeout(() => {
        copySuccess.value = false;
    }, 2500);
};
</script>

<template>
    <PublicLayout>
        <Head title="Coba Demo Instan 1-Klik (Sandbox 2 Jam) - AksaraEdu" />

        <div class="py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-10 max-w-2xl text-center">
                    <Badge variant="success" size="md" class="mb-3"
                        >Langsung Coba Tanpa Install</Badge
                    >
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl"
                    >
                        Coba Demo 1-Klik LMS & CBT AksaraEdu
                    </h1>
                    <p class="mt-3 text-sm text-slate-300 sm:text-base">
                        Dapatkan akun sandbox administrator live aktif selama 2
                        jam untuk mencoba CBT engine, modul Kurikulum Merdeka,
                        dan presensi QR.
                    </p>
                </div>

                <!-- Success Demo Sandbox Active Card -->
                <div v-if="demoResult" class="animate-in zoom-in-95">
                    <Card
                        class="border-emerald-500/50 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 p-6 shadow-2xl sm:p-10"
                    >
                        <div class="mb-8 text-center">
                            <div
                                class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/40 bg-emerald-500/20 text-emerald-400"
                            >
                                <Sparkles class="h-8 w-8" />
                            </div>
                            <h2 class="text-2xl font-bold text-white">
                                Sandbox Demo Instan Aktif!
                            </h2>
                            <p class="mt-1 text-xs text-emerald-300">
                                Database terisolasi khusus sekolah Anda telah
                                dibuat dan aktif selama 2 jam ke depan.
                            </p>
                        </div>

                        <!-- Credentials Box -->
                        <div
                            class="mx-auto mb-8 max-w-md space-y-3.5 rounded-2xl border border-slate-800 bg-slate-950/80 p-5"
                        >
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="text-slate-400">URL Sandbox:</span>
                                <span
                                    class="max-w-[220px] truncate font-mono font-bold text-emerald-400"
                                    >{{ demoResult.demo_url }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="text-slate-400"
                                    >Username Admin:</span
                                >
                                <span class="font-mono font-bold text-white">{{
                                    demoResult.username_demo
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="text-slate-400">Password:</span>
                                <span class="font-mono font-bold text-white">{{
                                    demoResult.password_demo
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-t border-slate-800/80 pt-2 text-xs text-amber-400"
                            >
                                <span class="flex items-center gap-1"
                                    ><Clock class="h-3.5 w-3.5" /> Masa
                                    Berlaku:</span
                                >
                                <span>2 Jam (Otomatis Reset)</span>
                            </div>
                        </div>

                        <div
                            class="flex flex-col items-center justify-center gap-3 sm:flex-row"
                        >
                            <Button
                                as="a"
                                :href="demoResult.demo_url"
                                target="_blank"
                                variant="primary"
                                size="lg"
                                class="w-full justify-center bg-emerald-500 font-bold hover:bg-emerald-600 sm:w-auto"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                Buka Aplikasi Demo Sekarang
                            </Button>
                            <Button
                                @click="copyCredentials"
                                variant="outline"
                                size="lg"
                                class="w-full justify-center border-slate-700 text-slate-200 sm:w-auto"
                            >
                                <Copy class="mr-2 h-4 w-4" />
                                {{
                                    copySuccess
                                        ? 'Berhasil Disalin!'
                                        : 'Salin Data Akses'
                                }}
                            </Button>
                        </div>
                    </Card>
                </div>

                <!-- Form Demo Launcher -->
                <div v-else>
                    <Card
                        class="mx-auto max-w-2xl border-slate-800 bg-slate-900 p-6 shadow-xl sm:p-10"
                    >
                        <form @submit.prevent="submitDemo" class="space-y-5">
                            <div
                                v-if="errorMessage"
                                class="rounded-lg border border-rose-500/40 bg-rose-950/50 p-3 text-xs text-rose-300"
                            >
                                {{ errorMessage }}
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Nama Lengkap PIC</label
                                    >
                                    <Input
                                        v-model="form.nama_pemohon"
                                        placeholder="Contoh: Ahmad Sanusi, S.Pd"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Nama Satuan Pendidikan</label
                                    >
                                    <Input
                                        v-model="form.nama_sekolah"
                                        placeholder="Contoh: SMK Negeri 2 Malang"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Nomor WhatsApp Aktif</label
                                    >
                                    <Input
                                        v-model="form.nomor_wa"
                                        placeholder="Contoh: 081299887766"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Email Resmi / Sekolah</label
                                    >
                                    <Input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Contoh: pic@smkn2malang.sch.id"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Jenjang Satuan Pendidikan</label
                                    >
                                    <select
                                        v-model="form.tipe_sekolah"
                                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-sm text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                    >
                                        <option value="smk">
                                            SMK (Sekolah Menengah Kejuruan)
                                        </option>
                                        <option value="sma">
                                            SMA (Sekolah Menengah Atas)
                                        </option>
                                        <option value="ma">
                                            MA (Madrasah Aliyah)
                                        </option>
                                        <option value="mak">
                                            MAK (Madrasah Aliyah Kejuruan)
                                        </option>
                                        <option value="smp">
                                            SMP (Sekolah Menengah Pertama)
                                        </option>
                                        <option value="mts">
                                            MTs (Madrasah Tsanawiyah)
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                                        >Minat Model Lisensi</label
                                    >
                                    <select
                                        v-model="form.model_minat"
                                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-sm text-slate-100 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                    >
                                        <option value="beli_putus">
                                            Beli Putus On-Premise (1x Bayar)
                                        </option>
                                        <option value="langganan">
                                            Langganan Cloud SaaS Tahunan
                                        </option>
                                        <option value="belum_tahu">
                                            Konsultasi Terlebih Dahulu
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                :loading="isSubmitting"
                                variant="primary"
                                size="lg"
                                class="mt-2 w-full justify-center bg-emerald-500 font-bold hover:bg-emerald-600"
                            >
                                <Zap class="mr-2 h-4 w-4" />
                                Buat Demo Sandbox Instan Sekarang
                            </Button>
                        </form>
                    </Card>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
