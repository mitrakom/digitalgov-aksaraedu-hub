<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import Button from '../../components/ui/Button.vue';
import Input from '../../components/ui/Input.vue';
import Card from '../../components/ui/Card.vue';
import { Lock, Mail, ShieldCheck, ArrowLeft, KeyRound } from 'lucide-vue-next';

const form = useForm({
    email: 'admin@aksaraedu.id',
    password: 'password123',
    remember: true,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const autofill = (email: string) => {
    form.email = email;
    form.password = 'password123';
};
</script>

<template>
    <Head title="Login Vendor HQ - AksaraEdu Central Hub" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-slate-950 p-4 font-sans text-slate-100 selection:bg-emerald-500 selection:text-white sm:p-6"
    >
        <!-- Glow Backdrop -->
        <div
            class="pointer-events-none absolute top-1/2 left-1/2 h-[300px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-600/10 blur-[120px]"
        ></div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Back to Landing Link -->
            <Link
                href="/"
                class="mb-6 inline-flex items-center gap-1.5 text-xs text-slate-400 transition-colors hover:text-emerald-400"
            >
                <ArrowLeft class="h-3.5 w-3.5" /> Kembali ke Portal Publik
            </Link>

            <!-- Brand Header -->
            <div class="mb-8 text-center">
                <div
                    class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 text-2xl font-extrabold text-white shadow-lg shadow-emerald-500/20"
                >
                    A
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">
                    Vendor Master Hub
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Unified Licensing Authority & Remote Control
                </p>
            </div>

            <!-- Login Card -->
            <Card class="border-slate-800 bg-slate-900 p-6 shadow-2xl sm:p-8">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label
                            class="mb-1.5 block flex items-center gap-1 text-xs font-semibold text-slate-300"
                        >
                            <Mail class="h-3.5 w-3.5 text-slate-400" /> Email
                            Vendor
                        </label>
                        <Input
                            v-model="form.email"
                            type="email"
                            placeholder="admin@aksaraedu.id"
                            :error="form.errors.email"
                            required
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block flex items-center gap-1 text-xs font-semibold text-slate-300"
                        >
                            <Lock class="h-3.5 w-3.5 text-slate-400" /> Kata
                            Sandi
                        </label>
                        <Input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            :error="form.errors.password"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <label
                            class="flex cursor-pointer items-center gap-2 text-slate-400 hover:text-slate-300"
                        >
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500/30"
                            />
                            <span>Ingat sesi masuk</span>
                        </label>
                        <span
                            class="flex items-center gap-1 text-[11px] text-slate-500"
                        >
                            <ShieldCheck class="h-3 w-3 text-emerald-400" />
                            RSA-4096 Secured
                        </span>
                    </div>

                    <Button
                        type="submit"
                        :loading="form.processing"
                        variant="primary"
                        size="lg"
                        class="mt-2 w-full justify-center bg-emerald-500 font-bold hover:bg-emerald-600"
                    >
                        <KeyRound class="mr-2 h-4 w-4" />
                        Masuk ke Vendor Portal
                    </Button>
                </form>

                <!-- Quick Autofill Demo Accounts -->
                <div class="mt-6 border-t border-slate-800/80 pt-5">
                    <p class="mb-2 text-[11px] font-semibold text-slate-400">
                        Akun Demo Cepat (Klik untuk isi):
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            type="button"
                            @click="autofill('admin@aksaraedu.id')"
                            class="hover:bg-slate-750 rounded-md border border-slate-700 bg-slate-800 px-2.5 py-1 text-[11px] text-slate-300"
                        >
                            Super Admin
                        </button>
                        <button
                            type="button"
                            @click="autofill('sales@aksaraedu.id')"
                            class="hover:bg-slate-750 rounded-md border border-slate-700 bg-slate-800 px-2.5 py-1 text-[11px] text-slate-300"
                        >
                            Sales Lead
                        </button>
                        <button
                            type="button"
                            @click="autofill('support@aksaraedu.id')"
                            class="hover:bg-slate-750 rounded-md border border-slate-700 bg-slate-800 px-2.5 py-1 text-[11px] text-slate-300"
                        >
                            DevOps & Support
                        </button>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
