<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from '../../../components/ui/Card.vue'
import Button from '../../../components/ui/Button.vue'
import Badge from '../../../components/ui/Badge.vue'
import Input from '../../../components/ui/Input.vue'
import {
  Activity,
  Search,
  Users,
  GraduationCap,
  FileCheck2,
  Server,
  Clock,
  Radio
} from 'lucide-vue-next'

interface Props {
  logs: any
  filters: any
  summary: {
    total_siswa: number
    total_guru: number
    total_ujian: number
  }
}

const props = defineProps<Props>()
const search = ref(props.filters.search || '')

const handleSearch = () => {
  router.get('/admin/telemetri', { search: search.value }, { preserveState: true, replace: true })
}
</script>

<template>
  <AdminLayout>
    <Head title="Monitor Telemetri & Heartbeat Realtime - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Monitor Telemetri & Heartbeat Realtime</h1>
    </template>

    <div class="space-y-6">
      <!-- Aggregated Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Card class="bg-slate-900 border-slate-800 p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-400">Total Siswa Aktif Terpantau</span>
            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <Users class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-white mt-2">{{ summary.total_siswa.toLocaleString() }}</p>
          <p class="text-[11px] text-emerald-400 mt-1">Data agregat telemetri instans klien</p>
        </Card>

        <Card class="bg-slate-900 border-slate-800 p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-400">Total Tenaga Pendidik (Guru)</span>
            <div class="p-2 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20">
              <GraduationCap class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-white mt-2">{{ summary.total_guru.toLocaleString() }}</p>
          <p class="text-[11px] text-teal-400 mt-1">Guru pengampu & wali kelas aktif</p>
        </Card>

        <Card class="bg-slate-900 border-slate-800 p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-400">Sesi Ujian CBT Dijalankan</span>
            <div class="p-2 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20">
              <FileCheck2 class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-white mt-2">{{ summary.total_ujian.toLocaleString() }}</p>
          <p class="text-[11px] text-sky-400 mt-1">Akumulasi asesmen formatif & sumatif</p>
        </Card>
      </div>

      <!-- Search Bar -->
      <div class="flex items-center gap-3">
        <div class="relative flex-1">
          <Input
            v-model="search"
            placeholder="Cari berdasarkan sekolah, IP address, domain, atau versi LMS..."
            @keyup.enter="handleSearch"
          />
        </div>
        <Button @click="handleSearch" variant="secondary" size="sm">
          <Search class="w-3.5 h-3.5 mr-1" /> Cari
        </Button>
      </div>

      <!-- Heartbeat Log Table -->
      <Card class="bg-slate-900 border-slate-800">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Sekolah / Instans</th>
                <th class="py-3 px-4">Domain & IP</th>
                <th class="py-3 px-4">Versi Software</th>
                <th class="py-3 px-4">Metrik Penggunaan</th>
                <th class="py-3 px-4 text-right">Waktu Ping (Heartbeat)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4">
                  <p class="font-bold text-white">{{ log.lisensi?.klien_sekolah?.nama_sekolah || 'Instans Terdaftar' }}</p>
                  <p class="text-[10px] font-mono text-emerald-400">NPSN: {{ log.lisensi?.klien_sekolah?.npsn }}</p>
                </td>
                <td class="py-3 px-4">
                  <p class="text-slate-200 font-medium">{{ log.domain_terdeteksi }}</p>
                  <p class="text-[11px] font-mono text-slate-500">{{ log.ip_address }}</p>
                </td>
                <td class="py-3 px-4">
                  <Badge variant="success" size="sm" class="font-mono">LMS v{{ log.versi_lms }}</Badge>
                  <p v-if="log.versi_php" class="text-[10px] text-slate-400 mt-0.5">PHP {{ log.versi_php }}</p>
                </td>
                <td class="py-3 px-4">
                  <div class="space-y-0.5">
                    <p class="text-white font-semibold">{{ log.total_siswa_aktif }} Siswa | {{ log.total_guru_aktif }} Guru</p>
                    <p class="text-[11px] text-slate-400">{{ log.total_rombel_aktif }} Rombel | {{ log.total_ujian_cbt }} CBT Tests</p>
                  </div>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="inline-flex items-center gap-1 text-slate-300">
                    <Clock class="w-3 h-3 text-emerald-400" />
                    <span>{{ new Date(log.waktu_ping).toLocaleString('id-ID') }}</span>
                  </div>
                </td>
              </tr>
              <tr v-if="logs.data.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                  Tidak ada rekaman telemetri yang sesuai.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  </AdminLayout>
</template>
