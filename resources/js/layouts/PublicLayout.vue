<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Button from '../components/ui/Button.vue'
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
  ChevronRight
} from 'lucide-vue-next'

const mobileMenuOpen = ref(false)
const page = usePage()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white font-sans">
    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-emerald-900/80 via-teal-900/60 to-slate-900 border-b border-emerald-500/20 px-4 py-2 text-center text-xs sm:text-sm text-emerald-200 flex items-center justify-center gap-2">
      <Sparkles class="w-4 h-4 text-emerald-400 shrink-0" />
      <span><strong>AksaraEdu v1.0.1 Dirilis:</strong> CBT Lite Anti-Ngadat & Integrasi Kurikulum Merdeka Otomatis.</span>
      <Link href="/demo" class="underline underline-offset-2 font-semibold hover:text-white inline-flex items-center gap-0.5 ml-1">
        Coba Demo Gratis <ChevronRight class="w-3.5 h-3.5" />
      </Link>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Brand Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
            <span class="text-white font-extrabold text-2xl tracking-tighter">A</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-xl tracking-tight text-white group-hover:text-emerald-400 transition-colors">AksaraEdu</span>
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Hub</span>
            </div>
            <p class="text-[11px] text-slate-400 font-normal tracking-wide">Central Platform & Licensing</p>
          </div>
        </Link>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-1 bg-slate-900/60 p-1.5 rounded-full border border-slate-800/80">
          <Link
            href="/"
            class="px-4 py-2 text-sm font-medium rounded-full transition-colors"
            :class="page.url === '/' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
          >
            Beranda
          </Link>
          <Link
            href="/pricing"
            class="px-4 py-2 text-sm font-medium rounded-full transition-colors flex items-center gap-1.5"
            :class="page.url.startsWith('/pricing') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
          >
            <Calculator class="w-4 h-4" />
            Kalkulator Lisensi
          </Link>
          <Link
            href="/verify"
            class="px-4 py-2 text-sm font-medium rounded-full transition-colors flex items-center gap-1.5"
            :class="page.url.startsWith('/verify') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
          >
            <ShieldCheck class="w-4 h-4" />
            Cek Lisensi NPSN
          </Link>
          <Link
            href="/demo"
            class="px-4 py-2 text-sm font-medium rounded-full transition-colors flex items-center gap-1.5"
            :class="page.url.startsWith('/demo') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'"
          >
            <PlayCircle class="w-4 h-4" />
            Demo 1-Klik
          </Link>
        </nav>

        <!-- Right Action Buttons -->
        <div class="hidden md:flex items-center gap-3">
          <Button
            as="link"
            href="/login"
            variant="outline"
            size="sm"
            class="border-slate-700 text-slate-300 hover:text-white hover:border-slate-600"
          >
            <Lock class="w-3.5 h-3.5 mr-1" />
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
          class="md:hidden p-2 rounded-lg bg-slate-900 text-slate-300 hover:text-white border border-slate-800"
        >
          <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
          <X v-else class="w-6 h-6" />
        </button>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-slate-900 border-b border-slate-800 px-4 pt-3 pb-5 space-y-2">
        <Link
          href="/"
          class="block px-3 py-2 text-sm font-medium rounded-lg text-slate-200 hover:bg-slate-800"
          @click="mobileMenuOpen = false"
        >
          Beranda
        </Link>
        <Link
          href="/pricing"
          class="block px-3 py-2 text-sm font-medium rounded-lg text-slate-200 hover:bg-slate-800"
          @click="mobileMenuOpen = false"
        >
          Kalkulator Lisensi & Unduh Penawaran PDF
        </Link>
        <Link
          href="/verify"
          class="block px-3 py-2 text-sm font-medium rounded-lg text-slate-200 hover:bg-slate-800"
          @click="mobileMenuOpen = false"
        >
          Verifikasi Lisensi NPSN
        </Link>
        <Link
          href="/demo"
          class="block px-3 py-2 text-sm font-medium rounded-lg text-emerald-400 font-semibold hover:bg-slate-800"
          @click="mobileMenuOpen = false"
        >
          Coba Demo 1-Klik Instan
        </Link>
        <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
          <Button as="link" href="/login" variant="outline" size="sm" class="w-full justify-center">
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
      class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-full shadow-xl shadow-emerald-900/40 border border-emerald-400/30 transition-all hover:scale-105 active:scale-95 group"
    >
      <MessageCircle class="w-5 h-5 text-white" />
      <span class="text-sm font-semibold hidden sm:inline">Konsultasi WhatsApp</span>
    </a>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 pt-14 pb-10 text-slate-400 text-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-10">
        <!-- Col 1: Brand -->
        <div class="space-y-4 md:col-span-1">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white font-extrabold text-lg">
              A
            </div>
            <span class="font-bold text-lg text-white">AksaraEdu Central Hub</span>
          </div>
          <p class="text-xs text-slate-400 leading-relaxed">
            Pusat manajemen lisensi berstandar RSA-4096, distribusi pembaruan resmi, dan portal marketing LMS & CBT AksaraEdu untuk seluruh satuan pendidikan Indonesia.
          </p>
          <div class="flex items-center gap-2 pt-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[11px] text-slate-300">
              <Lock class="w-3 h-3 text-emerald-400" /> RSA-4096 Cryptography
            </span>
          </div>
        </div>

        <!-- Col 2: Produk & Fitur -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">Produk & Ekosistem</h4>
          <ul class="space-y-2 text-xs">
            <li><Link href="/" class="hover:text-emerald-400 transition-colors">LMS & CBT Engine Lite</Link></li>
            <li><Link href="/pricing" class="hover:text-emerald-400 transition-colors">Beli Putus On-Premise (Full Source)</Link></li>
            <li><Link href="/pricing" class="hover:text-emerald-400 transition-colors">Langganan Cloud SaaS</Link></li>
            <li><Link href="/demo" class="hover:text-emerald-400 transition-colors">Sandbox Demo Interaktif 2 Jam</Link></li>
          </ul>
        </div>

        <!-- Col 3: Layanan & Verifikasi -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">Keamanan & Layanan</h4>
          <ul class="space-y-2 text-xs">
            <li><Link href="/verify" class="hover:text-emerald-400 transition-colors">Cek Status Lisensi NPSN Resmi</Link></li>
            <li><span class="text-slate-500">Garansi Bugfix 3 Bulan (Beli Putus)</span></li>
            <li><span class="text-slate-500">Central Update & Patch Registry</span></li>
            <li><span class="text-slate-500">Zero Phoning Home Mode (Offline)</span></li>
          </ul>
        </div>

        <!-- Col 4: Akses Vendor & Kontak -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">Vendor HQ</h4>
          <p class="text-xs text-slate-400">
            Akses khusus tim manajemen, sales CRM, dan devops AksaraEdu untuk mengelola lisensi klien.
          </p>
          <Button as="link" href="/login" variant="outline" size="sm" class="w-full justify-center border-slate-700 hover:border-slate-500">
            Masuk ke Vendor Portal
          </Button>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <p>&copy; 2026 AksaraEdu Central Hub. Seluruh hak cipta dilindungi undang-undang.</p>
        <div class="flex items-center gap-4">
          <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> RSA Licensing Engine Online</span>
        </div>
      </div>
    </footer>
  </div>
</template>
