<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '../../layouts/AdminLayout.vue'
import Card from '../../components/ui/Card.vue'
import Badge from '../../components/ui/Badge.vue'
import Button from '../../components/ui/Button.vue'
import {
  School,
  KeyRound,
  Server,
  Cloud,
  Coins,
  AlertTriangle,
  Activity,
  LifeBuoy,
  Users2,
  ShieldCheck,
  ArrowUpRight,
  Clock,
  ChevronRight,
  ExternalLink
} from 'lucide-vue-next'

interface Props {
  stats: {
    total_klien: number
    total_beli_putus: number
    total_langganan: number
    total_revenue: number
    total_leads_baru: number
    total_tiket_open: number
    active_warranty_count: number
  }
  expiringLicenses: any[]
  recentTelemetry: any[]
  pendingTickets: any[]
  recentLeads: any[]
}

defineProps<Props>()

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Eksekutif Vendor - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Dashboard Eksekutif Vendor</h1>
    </template>

    <!-- Top Executive Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Klien -->
      <Card class="bg-slate-900 border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400">Total Sekolah Mitra</span>
          <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <School class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-white mt-2">{{ stats.total_klien }}</p>
        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
          <span class="text-emerald-400 font-semibold">{{ stats.total_beli_putus }} Beli Putus</span>
          <span>•</span>
          <span class="text-teal-400 font-semibold">{{ stats.total_langganan }} SaaS Cloud</span>
        </div>
      </Card>

      <!-- Total Revenue -->
      <Card class="bg-slate-900 border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400">Total Nilai Kontrak</span>
          <div class="p-2 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20">
            <Coins class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-white mt-2">{{ formatCurrency(stats.total_revenue) }}</p>
        <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1">
          <ShieldCheck class="w-3 h-3" /> {{ stats.active_warranty_count }} Klien dalam Garansi Aktif
        </p>
      </Card>

      <!-- Leads Baru -->
      <Card class="bg-slate-900 border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400">Prospek / Leads Demo</span>
          <div class="p-2 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20">
            <Users2 class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-white mt-2">{{ stats.total_leads_baru }}</p>
        <Link href="/admin/leads" class="text-[11px] text-sky-400 hover:underline mt-1 inline-flex items-center gap-1">
          Lihat Pipeline Sales CRM <ArrowUpRight class="w-3 h-3" />
        </Link>
      </Card>

      <!-- Tiket Open -->
      <Card class="bg-slate-900 border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400">Tiket Dukungan Terbuka</span>
          <div class="p-2 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <LifeBuoy class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-white mt-2">{{ stats.total_tiket_open }}</p>
        <Link href="/admin/tiket" class="text-[11px] text-amber-400 hover:underline mt-1 inline-flex items-center gap-1">
          Buka Helpdesk SLA <ArrowUpRight class="w-3 h-3" />
        </Link>
      </Card>
    </div>

    <!-- Alert Section: Expiry Watchlist (< 30 Hari) -->
    <div v-if="expiringLicenses.length > 0" class="p-5 rounded-2xl bg-amber-950/30 border border-amber-500/40">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <AlertTriangle class="w-4 h-4 text-amber-400" />
          <h3 class="text-xs font-bold uppercase tracking-wider text-amber-300">
            Peringatan Kontrak Mendekati Jatuh Tempo (Expiry Watchlist &lt; 30 Hari)
          </h3>
        </div>
        <Badge variant="warning">{{ expiringLicenses.length }} Sekolah</Badge>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="lic in expiringLicenses"
          :key="lic.id"
          class="p-3.5 rounded-xl bg-slate-900/80 border border-amber-500/30 flex items-center justify-between"
        >
          <div>
            <p class="text-xs font-bold text-white">{{ lic.klien_sekolah?.nama_sekolah }}</p>
            <p class="text-[11px] text-amber-400 mt-0.5">
              Jatuh tempo: {{ lic.tanggal_kadaluarsa }} ({{ lic.nomor_lisensi }})
            </p>
          </div>
          <Link
            :href="`/admin/lisensi?search=${lic.nomor_lisensi}`"
            class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/40"
          >
            Perpanjang
          </Link>
        </div>
      </div>
    </div>

    <!-- Two Columns: Telemetry Feed & Recent Leads -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left: Realtime Telemetry Live Heartbeat Feed -->
      <div class="lg:col-span-7">
        <Card class="bg-slate-900 border-slate-800 p-6 h-full flex flex-col">
          <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2">
              <Activity class="w-4 h-4 text-emerald-400 animate-pulse" />
              <h2 class="text-sm font-bold text-white">Live Heartbeat & Telemetri Klien</h2>
            </div>
            <Link href="/admin/telemetri" class="text-xs text-emerald-400 hover:underline">Lihat Semua</Link>
          </div>

          <div class="divide-y divide-slate-800/80 flex-1 overflow-y-auto mt-2">
            <div
              v-for="log in recentTelemetry"
              :key="log.id"
              class="py-3 flex items-center justify-between text-xs"
            >
              <div class="space-y-0.5">
                <p class="font-semibold text-slate-200">{{ log.lisensi?.klien_sekolah?.nama_sekolah || 'Sekolah Mitra' }}</p>
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                  <span>{{ log.domain_terdeteksi }}</span>
                  <span>•</span>
                  <span class="text-emerald-400 font-mono">v{{ log.versi_lms }}</span>
                </div>
              </div>
              <div class="text-right">
                <p class="font-bold text-white">{{ log.total_siswa_aktif }} Siswa</p>
                <p class="text-[10px] text-slate-500">{{ new Date(log.waktu_ping).toLocaleTimeString('id-ID') }}</p>
              </div>
            </div>
            <div v-if="recentTelemetry.length === 0" class="py-8 text-center text-xs text-slate-500">
              Belum ada log telemetri yang masuk.
            </div>
          </div>
        </Card>
      </div>

      <!-- Right: Pending Support Tickets & Leads Pipeline -->
      <div class="lg:col-span-5 space-y-6">
        <!-- Helpdesk Box -->
        <Card class="bg-slate-900 border-slate-800 p-6">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
            <div class="flex items-center gap-2">
              <LifeBuoy class="w-4 h-4 text-amber-400" />
              <h2 class="text-sm font-bold text-white">Tiket Dukungan Terbaru</h2>
            </div>
            <Link href="/admin/tiket" class="text-xs text-amber-400 hover:underline">Helpdesk</Link>
          </div>
          <div class="space-y-2.5">
            <div
              v-for="t in pendingTickets"
              :key="t.id"
              class="p-3 rounded-xl bg-slate-800/60 border border-slate-750 text-xs"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="font-mono font-bold text-slate-300">{{ t.nomor_tiket }}</span>
                <Badge :variant="t.prioritas === 'kritis' ? 'danger' : 'warning'">{{ t.prioritas }}</Badge>
              </div>
              <p class="font-semibold text-white truncate">{{ t.judul_masalah }}</p>
              <p class="text-[11px] text-slate-400 mt-1">{{ t.klien_sekolah?.nama_sekolah }}</p>
            </div>
            <div v-if="pendingTickets.length === 0" class="py-4 text-center text-xs text-slate-500">
              Semua tiket bantuan telah diselesaikan.
            </div>
          </div>
        </Card>

        <!-- Recent Leads Box -->
        <Card class="bg-slate-900 border-slate-800 p-6">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
            <div class="flex items-center gap-2">
              <Users2 class="w-4 h-4 text-sky-400" />
              <h2 class="text-sm font-bold text-white">Leads Demo Masuk</h2>
            </div>
            <Link href="/admin/leads" class="text-xs text-sky-400 hover:underline">CRM Sales</Link>
          </div>
          <div class="space-y-2.5">
            <div
              v-for="lead in recentLeads"
              :key="lead.id"
              class="p-3 rounded-xl bg-slate-800/60 border border-slate-750 text-xs flex items-center justify-between"
            >
              <div>
                <p class="font-bold text-white">{{ lead.nama_sekolah }}</p>
                <p class="text-[11px] text-slate-400">{{ lead.nama_pemohon }} ({{ lead.nomor_wa }})</p>
              </div>
              <Badge variant="info">Baru</Badge>
            </div>
            <div v-if="recentLeads.length === 0" class="py-4 text-center text-xs text-slate-500">
              Belum ada leads demo baru.
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
