<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
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
  Menu,
  X,
  ShieldCheck,
  CheckCircle2,
  AlertCircle,
  ExternalLink
} from 'lucide-vue-next'

const page = usePage()
const sidebarOpen = ref(false)
const flash = computed(() => ((page.props as any).flash as { success?: string; error?: string } | undefined) || {})
const userRole = computed(() => (page.props as any).auth?.user?.role)

const navItems = [
  { name: 'Dashboard Eksekutif', href: '/admin', icon: LayoutDashboard },
  { name: 'Sekolah Mitra (CRM)', href: '/admin/klien', icon: School },
  { name: 'Master Lisensi & RSA', href: '/admin/lisensi', icon: KeyRound },
  { name: 'Telemetri & Heartbeat', href: '/admin/telemetri', icon: Activity },
  { name: 'Repositori Rilis & Patch', href: '/admin/rilis', icon: Package },
  { name: 'Tiket Dukungan (SLA)', href: '/admin/tiket', icon: LifeBuoy },
  { name: 'Leads Demo (Sales)', href: '/admin/leads', icon: Users2 },
  { name: 'Siaran Remote Klien', href: '/admin/pengumuman', icon: Radio },
  { name: 'Tim & Pengguna Vendor', href: '/admin/pengguna', icon: UserCog, superAdminOnly: true },
]

const visibleNavItems = computed(() => {
  return navItems.filter(item => {
    if (item.superAdminOnly && userRole.value !== 'super_admin') {
      return false
    }
    return true
  })
})

const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-950 text-slate-100 font-sans">
    <!-- Sidebar for Desktop -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-slate-900 border-r border-slate-800/80 shrink-0">
      <!-- Brand Logo -->
      <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800/80">
        <Link href="/admin" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-emerald-900/30">
            A
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="font-bold text-base tracking-tight text-white">AksaraEdu</span>
              <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">HQ</span>
            </div>
            <p class="text-[10px] text-slate-400 font-normal">Vendor Central Control</p>
          </div>
        </Link>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
        <Link
          v-for="item in visibleNavItems"
          :key="item.name"
          :href="item.href"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 group"
          :class="page.url === item.href || (item.href !== '/admin' && page.url.startsWith(item.href))
            ? 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30 shadow-sm'
            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 transition-transform group-hover:scale-110"
            :class="page.url === item.href || (item.href !== '/admin' && page.url.startsWith(item.href))
              ? 'text-emerald-400'
              : 'text-slate-400 group-hover:text-slate-200'"
          />
          <span class="flex-1">{{ item.name }}</span>
          <ChevronRight
            v-if="page.url === item.href || (item.href !== '/admin' && page.url.startsWith(item.href))"
            class="w-3.5 h-3.5 text-emerald-400"
          />
        </Link>
      </nav>

      <!-- Bottom User Profile & Logout -->
      <div class="p-4 border-t border-slate-800/80 bg-slate-900/60">
        <div class="flex items-center justify-between gap-3 mb-3 px-2">
          <div class="flex items-center gap-2.5 overflow-hidden">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-bold flex items-center justify-center text-xs">
              {{ page.props.auth?.user?.name ? page.props.auth.user.name[0] : 'A' }}
            </div>
            <div class="overflow-hidden">
              <p class="text-xs font-semibold text-slate-200 truncate">{{ page.props.auth?.user?.name || 'Admin HQ' }}</p>
              <span class="inline-block text-[10px] font-mono text-emerald-400 uppercase">
                {{ page.props.auth?.user?.role || 'Super Admin' }}
              </span>
            </div>
          </div>
          <button
            @click="logout"
            title="Keluar / Logout"
            class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>

        <Link
          href="/"
          target="_blank"
          class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-800 text-[11px] text-slate-300 font-medium transition-colors"
        >
          <ExternalLink class="w-3 h-3 text-slate-400" />
          Buka Landing Page Publik
        </Link>
      </div>
    </aside>

    <!-- Mobile Sidebar Drawer -->
    <div v-if="sidebarOpen" class="lg:hidden fixed inset-0 z-50 flex">
      <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs" @click="sidebarOpen = false"></div>
      <div class="relative w-64 bg-slate-900 h-full p-6 flex flex-col z-10">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
          <span class="font-bold text-lg text-white">AksaraEdu HQ</span>
          <button @click="sidebarOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-white">
            <X class="w-5 h-5" />
          </button>
        </div>
        <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
          <Link
            v-for="item in visibleNavItems"
            :key="item.name"
            :href="item.href"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800"
            @click="sidebarOpen = false"
          >
            <component :is="item.icon" class="w-4 h-4" />
            <span>{{ item.name }}</span>
          </Link>
        </nav>
        <button
          @click="logout"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-400 hover:bg-slate-800"
        >
          <LogOut class="w-4 h-4" />
          <span>Keluar Sesi</span>
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar Header -->
      <header class="h-16 bg-slate-900/80 border-b border-slate-800/80 backdrop-blur-md px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
          <button
            @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white bg-slate-800"
          >
            <Menu class="w-5 h-5" />
          </button>
          <div>
            <slot name="header-title">
              <h1 class="text-base font-bold text-slate-100 tracking-tight">Vendor Master Hub</h1>
            </slot>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- RSA Master Status Badge -->
          <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/60 border border-emerald-700/40 text-emerald-300 text-xs font-medium">
            <ShieldCheck class="w-3.5 h-3.5 text-emerald-400" />
            <span>RSA-4096 Engine Online</span>
          </div>
        </div>
      </header>

      <!-- Flash Notification Message -->
      <div v-if="flash.success" class="bg-emerald-900/60 border-b border-emerald-500/30 px-6 py-2.5 flex items-center justify-between text-xs text-emerald-200 animate-in fade-in">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-400" />
          <span>{{ flash.success }}</span>
        </div>
      </div>
      <div v-if="flash.error" class="bg-rose-900/60 border-b border-rose-500/30 px-6 py-2.5 flex items-center justify-between text-xs text-rose-200 animate-in fade-in">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-400" />
          <span>{{ flash.error }}</span>
        </div>
      </div>

      <!-- Main Page Slot -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto bg-slate-950/90">
        <div class="max-w-7xl mx-auto space-y-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
