<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    School,
    KeyRound,
    Activity,
    Package,
    LifeBuoy,
    Users2,
    Radio,
    UserCog,
    LogOut,
    ChevronRight,
    ChevronDown,
    Menu,
    X,
    ShieldCheck,
    CheckCircle2,
    AlertCircle,
    ExternalLink,
    Sparkles,
} from 'lucide-vue-next';

const page = usePage();
const sidebarOpen = ref(false);
const flash = computed(
    () =>
        ((page.props as any).flash as
            | { success?: string; error?: string }
            | undefined) || {},
);
const userRole = computed(() => (page.props as any).auth?.user?.role);

// 1. Menu Utama
const primaryNavItems = [
    { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { name: 'Sekolah', href: '/admin/klien', icon: School },
    { name: 'Pembaruan', href: '/admin/rilis', icon: Package },
];

// 2. Akses & Administrasi
const teamNavItems = [
    {
        name: 'Pengguna',
        href: '/admin/pengguna',
        icon: UserCog,
        superAdminOnly: true,
    },
];

// 3. Menu Tambahan (Sekunder)
const extensionNavItems = [
    { name: 'Bantuan', href: '/admin/tiket', icon: LifeBuoy },
    { name: 'Prospek', href: '/admin/leads', icon: Users2 },
    { name: 'Pengumuman', href: '/admin/pengumuman', icon: Radio },
];

const isExtensionActive = computed(() => {
    return extensionNavItems.some(
        (item) => page.url === item.href || page.url.startsWith(item.href),
    );
});

const showExtensions = ref(isExtensionActive.value);

const visibleTeamNavItems = computed(() => {
    return teamNavItems.filter((item) => {
        if (item.superAdminOnly && userRole.value !== 'super_admin') {
            return false;
        }
        return true;
    });
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="flex min-h-screen bg-slate-950 font-sans text-slate-100">
        <!-- Sidebar for Desktop -->
        <aside
            class="hidden w-64 shrink-0 border-r border-slate-800/80 bg-slate-900 lg:flex lg:flex-col"
        >
            <!-- Brand Logo -->
            <div
                class="flex h-20 items-center justify-between border-b border-slate-800/80 px-6"
            >
                <Link href="/admin" class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-xl font-extrabold text-white shadow-md shadow-emerald-900/30"
                    >
                        A
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-base font-bold tracking-tight text-white"
                                >AksaraEdu</span
                            >
                            <span
                                class="rounded border border-emerald-500/40 bg-emerald-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-emerald-400 uppercase"
                                >HQ</span
                            >
                        </div>
                        <p class="text-[10px] font-normal text-slate-400">
                            Vendor Central Hub
                        </p>
                    </div>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6">
                <!-- Group 1: Menu Utama -->
                <div class="space-y-1">
                    <p
                        class="px-3 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Menu Utama
                    </p>
                    <Link
                        v-for="item in primaryNavItems"
                        :key="item.name"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all duration-150"
                        :class="
                            page.url === item.href ||
                            (item.href !== '/admin' &&
                                page.url.startsWith(item.href))
                                ? 'border border-emerald-500/30 bg-emerald-600/20 text-emerald-300 shadow-xs'
                                : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-4 w-4 transition-transform group-hover:scale-110"
                            :class="
                                page.url === item.href ||
                                (item.href !== '/admin' &&
                                    page.url.startsWith(item.href))
                                    ? 'text-emerald-400'
                                    : 'text-slate-400 group-hover:text-slate-200'
                            "
                        />
                        <span class="flex-1">{{ item.name }}</span>
                        <ChevronRight
                            v-if="
                                page.url === item.href ||
                                (item.href !== '/admin' &&
                                    page.url.startsWith(item.href))
                            "
                            class="h-3.5 w-3.5 text-emerald-400"
                        />
                    </Link>
                </div>

                <!-- Group 2: Tim & Pengguna Vendor -->
                <div v-if="visibleTeamNavItems.length > 0" class="space-y-1">
                    <p
                        class="px-3 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Administrasi
                    </p>
                    <Link
                        v-for="item in visibleTeamNavItems"
                        :key="item.name"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all duration-150"
                        :class="
                            page.url === item.href ||
                            page.url.startsWith(item.href)
                                ? 'border border-emerald-500/30 bg-emerald-600/20 text-emerald-300 shadow-xs'
                                : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'
                        "
                    >
                        <component
                            :is="item.icon"
                            class="h-4 w-4 transition-transform group-hover:scale-110"
                            :class="
                                page.url === item.href ||
                                page.url.startsWith(item.href)
                                    ? 'text-emerald-400'
                                    : 'text-slate-400 group-hover:text-slate-200'
                            "
                        />
                        <span class="flex-1">{{ item.name }}</span>
                    </Link>
                </div>

                <!-- Group 3: Modul Ekstensi / Lanjutan (Collapsible) -->
                <div class="space-y-1">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase transition-colors hover:text-slate-200"
                        @click="showExtensions = !showExtensions"
                    >
                        <span class="flex items-center gap-1.5">
                            <span>Menu Tambahan</span>
                            <span
                                v-if="isExtensionActive"
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>
                        </span>
                        <ChevronDown
                            class="h-3.5 w-3.5 transition-transform duration-200"
                            :class="showExtensions ? 'rotate-180' : ''"
                        />
                    </button>

                    <div v-show="showExtensions" class="space-y-1 pt-1">
                        <Link
                            v-for="item in extensionNavItems"
                            :key="item.name"
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-xl px-3.5 py-2 text-xs font-medium transition-all duration-150"
                            :class="
                                page.url === item.href ||
                                page.url.startsWith(item.href)
                                    ? 'border border-emerald-500/30 bg-emerald-600/20 text-emerald-300'
                                    : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-300'
                            "
                        >
                            <component
                                :is="item.icon"
                                class="h-3.5 w-3.5 transition-transform group-hover:scale-110"
                                :class="
                                    page.url === item.href ||
                                    page.url.startsWith(item.href)
                                        ? 'text-emerald-400'
                                        : 'text-slate-400'
                                "
                            />
                            <span class="flex-1">{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Bottom User Profile & Logout -->
            <div class="border-t border-slate-800/80 bg-slate-900/60 p-4">
                <div class="mb-3 flex items-center justify-between gap-3 px-2">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-500/30 bg-emerald-500/20 text-xs font-bold text-emerald-300"
                        >
                            {{
                                page.props.auth?.user?.name
                                    ? page.props.auth.user.name[0]
                                    : 'A'
                            }}
                        </div>
                        <div class="overflow-hidden">
                            <p
                                class="truncate text-xs font-semibold text-slate-200"
                            >
                                {{ page.props.auth?.user?.name || 'Admin HQ' }}
                            </p>
                            <span
                                class="inline-block font-mono text-[10px] text-emerald-400 uppercase"
                            >
                                {{
                                    page.props.auth?.user?.role || 'Super Admin'
                                }}
                            </span>
                        </div>
                    </div>
                    <button
                        @click="logout"
                        title="Keluar / Logout"
                        class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-800 hover:text-rose-400"
                    >
                        <LogOut class="h-4 w-4" />
                    </button>
                </div>

                <Link
                    href="/"
                    target="_blank"
                    class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-slate-800/80 px-3 py-1.5 text-[11px] font-medium text-slate-300 transition-colors hover:bg-slate-800"
                >
                    <ExternalLink class="h-3 w-3 text-slate-400" />
                    Buka Landing Page Publik
                </Link>
            </div>
        </aside>

        <!-- Mobile Sidebar Drawer -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-50 flex lg:hidden">
            <div
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs"
                @click="sidebarOpen = false"
            ></div>
            <div
                class="relative z-10 flex h-full w-64 flex-col bg-slate-900 p-6"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-800 pb-4"
                >
                    <span class="text-lg font-bold text-white"
                        >AksaraEdu HQ</span
                    >
                    <button
                        @click="sidebarOpen = false"
                        class="rounded-lg p-1 text-slate-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <nav class="flex-1 space-y-4 overflow-y-auto py-4">
                    <div class="space-y-1">
                        <p
                            class="px-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Menu Utama
                        </p>
                        <Link
                            v-for="item in primaryNavItems"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800"
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>

                    <div
                        v-if="visibleTeamNavItems.length > 0"
                        class="space-y-1"
                    >
                        <p
                            class="px-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Administrasi
                        </p>
                        <Link
                            v-for="item in visibleTeamNavItems"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800"
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>

                    <div class="space-y-1">
                        <p
                            class="px-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Menu Tambahan
                        </p>
                        <Link
                            v-for="item in extensionNavItems"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-medium text-slate-400 hover:bg-slate-800"
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" class="h-3.5 w-3.5" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>
                </nav>
                <button
                    @click="logout"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-rose-400 hover:bg-slate-800"
                >
                    <LogOut class="h-4 w-4" />
                    <span>Keluar Sesi</span>
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <!-- Topbar Header -->
            <header
                class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800/80 bg-slate-900/80 px-4 backdrop-blur-md sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <button
                        @click="sidebarOpen = true"
                        class="rounded-lg bg-slate-800 p-2 text-slate-400 hover:text-white lg:hidden"
                    >
                        <Menu class="h-5 w-5" />
                    </button>
                    <div>
                        <slot name="header-title">
                            <h1
                                class="text-base font-bold tracking-tight text-slate-100"
                            >
                                Vendor Master Hub
                            </h1>
                        </slot>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- RSA Master Status Badge -->
                    <div
                        class="hidden items-center gap-1.5 rounded-full border border-emerald-700/40 bg-emerald-950/60 px-3 py-1 text-xs font-medium text-emerald-300 sm:flex"
                    >
                        <ShieldCheck class="h-3.5 w-3.5 text-emerald-400" />
                        <span>RSA-4096 Engine Online</span>
                    </div>
                </div>
            </header>

            <!-- Flash Notification Message -->
            <div
                v-if="flash.success"
                class="animate-in fade-in flex items-center justify-between border-b border-emerald-500/30 bg-emerald-900/60 px-6 py-2.5 text-xs text-emerald-200"
            >
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="h-4 w-4 text-emerald-400" />
                    <span>{{ flash.success }}</span>
                </div>
            </div>
            <div
                v-if="flash.error"
                class="animate-in fade-in flex items-center justify-between border-b border-rose-500/30 bg-rose-900/60 px-6 py-2.5 text-xs text-rose-200"
            >
                <div class="flex items-center gap-2">
                    <AlertCircle class="h-4 w-4 text-rose-400" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Main Page Slot -->
            <main
                class="flex-1 overflow-y-auto bg-slate-950/90 p-4 sm:p-6 lg:p-8"
            >
                <div class="mx-auto max-w-7xl space-y-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
