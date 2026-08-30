<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Button from '../components/ui/Button.vue';
import {
    ShieldCheck,
    Calculator,
    PlayCircle,
    Menu,
    X,
    Sparkles,
    Award,
    Lock,
    ArrowRight,
    MessageCircle,
    Cpu,
    Layers,
    ChevronRight,
} from 'lucide-vue-next';

const mobileMenuOpen = ref(false);
const page = usePage();
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-slate-950 font-sans text-slate-100 selection:bg-emerald-500 selection:text-white"
    >
        <!-- Top Announcement Bar -->
        <div
            class="flex items-center justify-center gap-2 border-b border-emerald-500/20 bg-gradient-to-r from-emerald-900/80 via-teal-900/60 to-slate-900 px-4 py-2 text-center text-xs text-emerald-200 sm:text-sm"
        >
            <Sparkles class="h-4 w-4 shrink-0 text-emerald-400" />
            <span
                ><strong>AksaraEdu v1.0.1 Dirilis:</strong> CBT Lite Anti-Ngadat
                & Integrasi Kurikulum Merdeka Otomatis.</span
            >
            <Link
                href="/demo"
                class="ml-1 inline-flex items-center gap-0.5 font-semibold underline underline-offset-2 hover:text-white"
            >
                Coba Demo Gratis <ChevronRight class="h-3.5 w-3.5" />
            </Link>
        </div>

        <!-- Navigation Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Brand Logo -->
                <Link href="/" class="group flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 shadow-lg shadow-emerald-500/20 transition-transform group-hover:scale-105"
                    >
                        <span
                            class="text-2xl font-extrabold tracking-tighter text-white"
                            >A</span
                        >
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xl font-bold tracking-tight text-white transition-colors group-hover:text-emerald-400"
                                >AksaraEdu</span
                            >
                            <span
                                class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold tracking-wider text-emerald-400 uppercase"
                                >Hub</span
                            >
                        </div>
                        <p
                            class="text-[11px] font-normal tracking-wide text-slate-400"
                        >
                            Central Platform & Licensing
                        </p>
                    </div>
                </Link>

                <!-- Desktop Navigation Links -->
                <nav
                    class="hidden items-center gap-1 rounded-full border border-slate-800/80 bg-slate-900/60 p-1.5 md:flex"
                >
                    <Link
                        href="/"
                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            page.url === '/'
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'
                        "
                    >
                        Beranda
                    </Link>
                    <Link
                        href="/pricing"
                        class="flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            page.url.startsWith('/pricing')
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'
                        "
                    >
                        <Calculator class="h-4 w-4" />
                        Kalkulator Lisensi
                    </Link>
                    <Link
                        href="/verify"
                        class="flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            page.url.startsWith('/verify')
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'
                        "
                    >
                        <ShieldCheck class="h-4 w-4" />
                        Cek Lisensi NPSN
                    </Link>
                    <Link
                        href="/demo"
                        class="flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            page.url.startsWith('/demo')
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'
                        "
                    >
                        <PlayCircle class="h-4 w-4" />
                        Demo 1-Klik
                    </Link>
                </nav>

                <!-- Right Action Buttons -->
                <div class="hidden items-center gap-3 md:flex">
                    <Button
                        as="link"
                        href="/login"
                        variant="outline"
                        size="sm"
                        class="border-slate-700 text-slate-300 hover:border-slate-600 hover:text-white"
                    >
                        <Lock class="mr-1 h-3.5 w-3.5" />
                        Portal Vendor
                    </Button>
                    <Button
                        as="link"
                        href="/demo"
                        variant="primary"
                        size="sm"
                        class="bg-emerald-500 hover:bg-emerald-600"
                    >
                        Coba Demo Gratis
                    </Button>
                </div>

                <!-- Mobile Hamburger Button -->
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="rounded-lg border border-slate-800 bg-slate-900 p-2 text-slate-300 hover:text-white md:hidden"
                >
                    <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                    <X v-else class="h-6 w-6" />
                </button>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div
                v-if="mobileMenuOpen"
                class="space-y-2 border-b border-slate-800 bg-slate-900 px-4 pt-3 pb-5 md:hidden"
            >
                <Link
                    href="/"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    Beranda
                </Link>
                <Link
                    href="/pricing"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    Kalkulator Lisensi & Unduh Penawaran PDF
                </Link>
                <Link
                    href="/verify"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    Verifikasi Lisensi NPSN
                </Link>
                <Link
                    href="/demo"
                    class="block rounded-lg px-3 py-2 text-sm font-medium font-semibold text-emerald-400 hover:bg-slate-800"
                    @click="mobileMenuOpen = false"
                >
                    Coba Demo 1-Klik Instan
                </Link>
                <div class="flex flex-col gap-2 border-t border-slate-800 pt-3">
                    <Button
                        as="link"
                        href="/login"
                        variant="outline"
                        size="sm"
                        class="w-full justify-center"
                    >
                        Login Vendor HQ
                    </Button>
                </div>
            </div>
        </header>

        <!-- Main Page Content Slot -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Floating WhatsApp Sales Consultation Button -->
        <a
            href="https://wa.me/6281234567890?text=Halo%20AksaraEdu%20Central%20Hub,%20saya%20tertarik%20konsultasi%20lisensi%20LMS%20dan%20CBT%20AksaraEdu%20untuk%20sekolah%20kami."
            target="_blank"
            rel="noopener noreferrer"
            class="group fixed right-6 bottom-6 z-50 flex items-center gap-2.5 rounded-full border border-emerald-400/30 bg-emerald-600 px-4 py-3 text-white shadow-xl shadow-emerald-900/40 transition-all hover:scale-105 hover:bg-emerald-500 active:scale-95"
        >
            <MessageCircle class="h-5 w-5 text-white" />
            <span class="hidden text-sm font-semibold sm:inline"
                >Konsultasi WhatsApp</span
            >
        </a>

        <!-- Footer -->
        <footer
            class="border-t border-slate-800/80 bg-slate-950 pt-14 pb-10 text-sm text-slate-400"
        >
            <div
                class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 sm:px-6 md:grid-cols-4 lg:px-8"
            >
                <!-- Col 1: Brand -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-lg font-extrabold text-white"
                        >
                            A
                        </div>
                        <span class="text-lg font-bold text-white"
                            >AksaraEdu Central Hub</span
                        >
                    </div>
                    <p class="text-xs leading-relaxed text-slate-400">
                        Pusat manajemen lisensi berstandar RSA-4096, distribusi
                        pembaruan resmi, dan portal marketing LMS & CBT
                        AksaraEdu untuk seluruh satuan pendidikan Indonesia.
                    </p>
                    <div class="flex items-center gap-2 pt-2">
                        <span
                            class="inline-flex items-center gap-1 rounded-md border border-slate-800 bg-slate-900 px-2.5 py-1 text-[11px] text-slate-300"
                        >
                            <Lock class="h-3 w-3 text-emerald-400" /> RSA-4096
                            Cryptography
                        </span>
                    </div>
                </div>

                <!-- Col 2: Produk & Fitur -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold tracking-wider text-slate-200 uppercase"
                    >
                        Produk & Ekosistem
                    </h4>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <Link
                                href="/"
                                class="transition-colors hover:text-emerald-400"
                                >LMS & CBT Engine Lite</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/pricing"
                                class="transition-colors hover:text-emerald-400"
                                >Beli Putus On-Premise (Full Source)</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/pricing"
                                class="transition-colors hover:text-emerald-400"
                                >Langganan Cloud SaaS</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/demo"
                                class="transition-colors hover:text-emerald-400"
                                >Sandbox Demo Interaktif 2 Jam</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Layanan & Verifikasi -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold tracking-wider text-slate-200 uppercase"
                    >
                        Keamanan & Layanan
                    </h4>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <Link
                                href="/verify"
                                class="transition-colors hover:text-emerald-400"
                                >Cek Status Lisensi NPSN Resmi</Link
                            >
                        </li>
                        <li>
                            <span class="text-slate-500"
                                >Garansi Bugfix 3 Bulan (Beli Putus)</span
                            >
                        </li>
                        <li>
                            <span class="text-slate-500"
                                >Central Update & Patch Registry</span
                            >
                        </li>
                        <li>
                            <span class="text-slate-500"
                                >Zero Phoning Home Mode (Offline)</span
                            >
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Akses Vendor & Kontak -->
                <div class="space-y-3">
                    <h4
                        class="text-xs font-bold tracking-wider text-slate-200 uppercase"
                    >
                        Vendor HQ
                    </h4>
                    <p class="text-xs text-slate-400">
                        Akses khusus tim manajemen, sales CRM, dan devops
                        AksaraEdu untuk mengelola lisensi klien.
                    </p>
                    <Button
                        as="link"
                        href="/login"
                        variant="outline"
                        size="sm"
                        class="w-full justify-center border-slate-700 hover:border-slate-500"
                    >
                        Masuk ke Vendor Portal
                    </Button>
                </div>
            </div>

            <div
                class="mx-auto mt-12 flex max-w-7xl flex-col items-center justify-between gap-4 border-t border-slate-900 px-4 pt-6 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8"
            >
                <p>
                    &copy; 2026 AksaraEdu Central Hub. Seluruh hak cipta
                    dilindungi undang-undang.
                </p>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5"
                        ><span
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></span>
                        RSA Licensing Engine Online</span
                    >
                </div>
            </div>
        </footer>
    </div>
</template>
