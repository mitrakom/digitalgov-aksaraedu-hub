<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '../../layouts/PublicLayout.vue'
import Button from '../../components/ui/Button.vue'
import Badge from '../../components/ui/Badge.vue'
import Card from '../../components/ui/Card.vue'
import Input from '../../components/ui/Input.vue'
import {
  ShieldCheck,
  Search,
  CheckCircle2,
  AlertTriangle,
  School,
  MapPin,
  Calendar,
  KeyRound,
  ShieldAlert
} from 'lucide-vue-next'

const npsnInput = ref('')
const isLoading = ref(false)
const searchResult = ref<any>(null)
const searchError = ref<string | null>(null)

const verifyNpsn = async () => {
  if (!npsnInput.value.trim()) return

  isLoading.value = true
  searchResult.value = null
  searchError.value = null

  try {
    const res = await fetch(`/api/v1/license/verify/${encodeURIComponent(npsnInput.value.trim())}`)
    const data = await res.json()

    if (res.ok && data.verified) {
      searchResult.value = data
    } else {
      searchError.value = data.message || 'NPSN sekolah tidak ditemukan dalam direktori lisensi resmi AksaraEdu.'
    }
  } catch (err) {
    searchError.value = 'Gagal menghubungi server verifikasi. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <PublicLayout>
    <Head title="Verifikasi Keaslian Lisensi NPSN - AksaraEdu Central Hub" />

    <div class="py-16">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <Badge variant="info" size="md" class="mb-3">Portal Resmi Verifikasi Nasional</Badge>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
          Cek Keaslian Lisensi Resmi AksaraEdu
        </h1>
        <p class="mt-3 text-sm sm:text-base text-slate-300 max-w-2xl mx-auto">
          Pastikan instans LMS & CBT di sekolah Anda memiliki lisensi legal bertanda tangan kriptografis RSA-4096 resmi dari AksaraEdu Central Hub.
        </p>

        <!-- NPSN Search Bar Form -->
        <div class="mt-10 max-w-xl mx-auto">
          <form @submit.prevent="verifyNpsn" class="flex flex-col sm:flex-row gap-2.5">
            <div class="relative flex-1">
              <Input
                v-model="npsnInput"
                placeholder="Masukkan 8 Digit NPSN Sekolah (Contoh: 20104050)"
                class="py-3.5 px-4 text-base bg-slate-900 border-slate-700 text-white rounded-xl focus:border-emerald-500"
              />
            </div>
            <Button
              type="submit"
              :loading="isLoading"
              variant="primary"
              size="lg"
              class="bg-emerald-500 hover:bg-emerald-600 rounded-xl justify-center font-bold"
            >
              <Search class="w-4 h-4 mr-2" />
              Verifikasi
            </Button>
          </form>
          <p class="text-[11px] text-slate-500 mt-2 text-center">
            Pengecekan langsung terhubung ke master cryptography licensing repository.
          </p>
        </div>

        <!-- Result Section: Verified Card -->
        <div v-if="searchResult" class="mt-12 text-left animate-in zoom-in-95">
          <Card class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-emerald-500/50 p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <!-- Glow Ribbon -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                  <ShieldCheck class="w-7 h-7" />
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Lisensi Terverifikasi Sah</span>
                    <Badge variant="success">Resmi Aktif</Badge>
                  </div>
                  <h3 class="text-xl font-bold text-white mt-0.5">{{ searchResult.nama_sekolah }}</h3>
                </div>
              </div>
              <div class="text-right">
                <p class="text-xs text-slate-400">Nomor Pokok Sekolah Nasional</p>
                <p class="text-lg font-mono font-bold text-white tracking-widest">{{ searchResult.npsn }}</p>
              </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 my-6 text-xs text-slate-300">
              <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-800">
                <p class="text-slate-400 mb-1 flex items-center gap-1.5"><School class="w-3.5 h-3.5 text-emerald-400" /> Jenjang / Tipe</p>
                <p class="text-sm font-bold text-white uppercase">{{ searchResult.tipe_sekolah }}</p>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-800">
                <p class="text-slate-400 mb-1 flex items-center gap-1.5"><MapPin class="w-3.5 h-3.5 text-teal-400" /> Wilayah / Kota</p>
                <p class="text-sm font-bold text-white">{{ searchResult.kabupaten_kota }}, {{ searchResult.provinsi }}</p>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-800">
                <p class="text-slate-400 mb-1 flex items-center gap-1.5"><KeyRound class="w-3.5 h-3.5 text-sky-400" /> Model Lisensi</p>
                <p class="text-sm font-bold text-white">{{ searchResult.model_lisensi }}</p>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-800">
                <p class="text-slate-400 mb-1 flex items-center gap-1.5"><Calendar class="w-3.5 h-3.5 text-amber-400" /> Tahun Penerbitan</p>
                <p class="text-sm font-bold text-white">Tahun {{ searchResult.tahun_terbit }}</p>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-800 sm:col-span-2">
                <p class="text-slate-400 mb-1 flex items-center gap-1.5"><ShieldCheck class="w-3.5 h-3.5 text-emerald-400" /> Status Garansi Bugfix Resmi</p>
                <div class="flex items-center gap-2 mt-0.5">
                  <Badge :variant="searchResult.garansi_aktif ? 'success' : 'warning'">
                    {{ searchResult.garansi_aktif ? 'Garansi Aktif' : 'Masa Garansi Berakhir' }}
                  </Badge>
                  <span v-if="searchResult.garansi_hingga" class="text-slate-300">Hingga {{ searchResult.garansi_hingga }}</span>
                </div>
              </div>
            </div>

            <div class="p-4 rounded-xl bg-emerald-950/30 border border-emerald-500/20 text-xs text-emerald-200 flex items-center gap-3">
              <ShieldCheck class="w-5 h-5 text-emerald-400 shrink-0" />
              <span>Instans ini terproteksi oleh lisensi resmi vendor AksaraEdu dengan enkripsi RSA-4096. Hak kekayaan intelektual terlindungi.</span>
            </div>
          </Card>
        </div>

        <!-- Result Section: Error Card -->
        <div v-else-if="searchError" class="mt-12 text-left animate-in zoom-in-95">
          <Card class="bg-slate-900 border-rose-500/50 p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-rose-500/20 border border-rose-500/40 text-rose-400 flex items-center justify-center mx-auto mb-4">
              <ShieldAlert class="w-6 h-6" />
            </div>
            <h3 class="text-lg font-bold text-white mb-1">NPSN Tidak Terdaftar Resmi</h3>
            <p class="text-xs text-rose-300 max-w-md mx-auto mb-6 leading-relaxed">{{ searchError }}</p>
            <Button as="link" href="/pricing" variant="outline" size="sm" class="border-slate-700 text-slate-300 hover:text-white">
              Ajukan Penerbitan Lisensi Baru
            </Button>
          </Card>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
