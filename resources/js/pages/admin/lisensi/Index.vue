<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from '../../../components/ui/Card.vue'
import Button from '../../../components/ui/Button.vue'
import Badge from '../../../components/ui/Badge.vue'
import Input from '../../../components/ui/Input.vue'
import Modal from '../../../components/ui/Modal.vue'
import {
  KeyRound,
  Plus,
  Search,
  Download,
  RotateCcw,
  ShieldAlert,
  ShieldCheck,
  Calendar,
  Copy,
  ExternalLink,
  Cpu
} from 'lucide-vue-next'

interface Props {
  lisensis: any
  kliens: any[]
  filters: any
  publicKey: string
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const modelFilter = ref(props.filters.model_lisensi || '')
const statusFilter = ref(props.filters.status || '')

const isCreateModalOpen = ref(false)
const isRenewModalOpen = ref(false)
const isKeyModalOpen = ref(false)
const selectedLicense = ref<any>(null)
const copyKeySuccess = ref(false)

const createForm = useForm({
  klien_sekolah_id: props.kliens.length > 0 ? props.kliens[0].id : '',
  model_lisensi: 'beli_putus',
  tier_paket: 'enterprise',
  domain_terdaftar: '',
  tanggal_rilis: new Date().toISOString().split('T')[0],
  durasi_bulan: 12,
  garansi_bulan: 3,
  nilai_kontrak: 15000000,
  catatan_kontrak: '',
})

const renewForm = useForm({
  perpanjang_bulan: 12,
  nilai_kontrak_tambahan: 6000000,
})

const handleFilter = () => {
  router.get('/admin/lisensi', {
    search: search.value,
    model_lisensi: modelFilter.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true })
}

const submitCreateLicense = () => {
  createForm.post('/admin/lisensi', {
    onSuccess: () => {
      isCreateModalOpen.value = false
      createForm.reset()
    },
  })
}

const openRenewModal = (lic: any) => {
  selectedLicense.value = lic
  isRenewModalOpen.value = true
}

const submitRenew = () => {
  if (!selectedLicense.value) return
  renewForm.post(`/admin/lisensi/${selectedLicense.value.id}/renew`, {
    onSuccess: () => {
      isRenewModalOpen.value = false
    },
  })
}

const resetHardware = (id: string) => {
  if (confirm('Reset kaitan hardware fingerprint? Server sekolah dapat melakukan binding ulang ke perangkat baru.')) {
    router.post(`/admin/lisensi/${id}/reset-hardware`)
  }
}

const revokeLicense = (id: string) => {
  if (confirm('Cabut lisensi ini? Klien tidak dapat lagi melakukan sinkronisasi dan status akan dibatalkan.')) {
    router.post(`/admin/lisensi/${id}/revoke`)
  }
}

const copyPublicKey = () => {
  navigator.clipboard.writeText(props.publicKey)
  copyKeySuccess.value = true
  setTimeout(() => {
    copyKeySuccess.value = false
  }, 2000)
}
</script>

<template>
  <AdminLayout>
    <Head title="Master Lisensi & Kriptografi RSA - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Master Lisensi & Kriptografi RSA</h1>
    </template>

    <div class="space-y-6">
      <!-- Top Action Bar -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
          <div class="relative flex-1">
            <Input
              v-model="search"
              placeholder="Cari nomor lisensi, serial key, domain, NPSN..."
              @keyup.enter="handleFilter"
            />
          </div>
          <select
            v-model="modelFilter"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200"
          >
            <option value="">Semua Model</option>
            <option value="beli_putus">Beli Putus</option>
            <option value="langganan">Langganan SaaS</option>
          </select>
          <select
            v-model="statusFilter"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200"
          >
            <option value="">Semua Status</option>
            <option value="active">Active</option>
            <option value="grace_period">Grace Period</option>
            <option value="expired">Expired</option>
            <option value="revoked">Revoked</option>
          </select>
          <Button @click="handleFilter" variant="secondary" size="sm">
            <Search class="w-3.5 h-3.5 mr-1" /> Filter
          </Button>
        </div>

        <div class="flex items-center gap-2">
          <Button @click="isKeyModalOpen = true" variant="outline" size="sm">
            <KeyRound class="w-4 h-4 mr-1.5 text-emerald-400" /> RSA Public Key
          </Button>
          <Button @click="isCreateModalOpen = true" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
            <Plus class="w-4 h-4 mr-1.5" /> Terbitkan Lisensi Baru
          </Button>
        </div>
      </div>

      <!-- License Table -->
      <Card class="bg-slate-900 border-slate-800">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Nomor Lisensi / Sekolah</th>
                <th class="py-3 px-4">Serial Key & Token</th>
                <th class="py-3 px-4">Model & Tier</th>
                <th class="py-3 px-4">Node & Hardware</th>
                <th class="py-3 px-4">Masa Aktif & Garansi</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
              <tr v-for="lic in lisensis.data" :key="lic.id" class="hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4">
                  <p class="font-mono font-bold text-white text-xs">{{ lic.nomor_lisensi }}</p>
                  <p class="font-semibold text-emerald-400 text-[11px] mt-0.5">{{ lic.klien_sekolah?.nama_sekolah }}</p>
                  <p class="text-[10px] text-slate-500">NPSN: {{ lic.klien_sekolah?.npsn }}</p>
                </td>
                <td class="py-3 px-4">
                  <div class="space-y-1 font-mono text-[11px]">
                    <p class="text-white bg-slate-800 px-2 py-0.5 rounded border border-slate-700 inline-block">
                      {{ lic.serial_key }}
                    </p>
                    <p class="text-slate-400 truncate max-w-[140px] text-[10px]">{{ lic.token_api }}</p>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="lic.model_lisensi === 'beli_putus' ? 'success' : 'info'" size="sm" class="mb-1">
                    {{ lic.model_lisensi === 'beli_putus' ? 'Beli Putus' : 'SaaS Cloud' }}
                  </Badge>
                  <p class="text-[10px] uppercase font-bold text-slate-400">{{ lic.tier_paket }}</p>
                </td>
                <td class="py-3 px-4">
                  <p class="text-slate-200 font-medium">{{ lic.domain_terdaftar || 'Belum di-bind' }}</p>
                  <div class="flex items-center gap-1 mt-0.5 text-[10px] text-slate-500 font-mono">
                    <Cpu class="w-3 h-3" />
                    <span>{{ lic.hardware_fingerprint ? 'Terkunci (Node Active)' : 'Bebas / Siap Bind' }}</span>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <p class="text-[11px] text-slate-300">
                    {{ lic.tanggal_kadaluarsa ? `Hingga ${lic.tanggal_kadaluarsa}` : 'Lifetime (Selamanya)' }}
                  </p>
                  <p class="text-[10px] text-emerald-400">
                    Garansi: {{ lic.garansi_bugfix_hingga || '-' }}
                  </p>
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="lic.status === 'active' ? 'success' : (lic.status === 'grace_period' ? 'warning' : 'danger')">
                    {{ lic.status }}
                  </Badge>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a
                      :href="`/admin/lisensi/${lic.id}/download`"
                      title="Unduh file aksaraedu.lic"
                      class="p-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 transition-colors"
                    >
                      <Download class="w-3.5 h-3.5" />
                    </a>
                    <button
                      v-if="lic.model_lisensi === 'langganan'"
                      @click="openRenewModal(lic)"
                      title="Perpanjang Kontrak Langganan"
                      class="p-1.5 rounded-lg bg-teal-600/20 hover:bg-teal-600 text-teal-300 hover:text-white border border-teal-500/30 transition-colors cursor-pointer"
                    >
                      <Calendar class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="resetHardware(lic.id)"
                      title="Reset Hardware Node Fingerprint"
                      class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors cursor-pointer"
                    >
                      <RotateCcw class="w-3.5 h-3.5" />
                    </button>
                    <button
                      v-if="lic.status !== 'revoked'"
                      @click="revokeLicense(lic.id)"
                      title="Cabut Lisensi (Revoke)"
                      class="p-1.5 rounded-lg bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 transition-colors cursor-pointer"
                    >
                      <ShieldAlert class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="lisensis.data.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                  Belum ada lisensi yang cocok dengan kriteria pencarian.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>

    <!-- Modal Terbitkan Lisensi Baru -->
    <Modal :show="isCreateModalOpen" @close="isCreateModalOpen = false" title="Terbitkan Lisensi Resmi AksaraEdu" maxWidth="lg">
      <form @submit.prevent="submitCreateLicense" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Satuan Pendidikan Mitra</label>
          <select
            v-model="createForm.klien_sekolah_id"
            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            required
          >
            <option v-for="k in kliens" :key="k.id" :value="k.id">
              {{ k.nama_sekolah }} (NPSN: {{ k.npsn }} - {{ k.tipe_sekolah.toUpperCase() }})
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Model Lisensi</label>
            <select
              v-model="createForm.model_lisensi"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="beli_putus">Beli Putus On-Premise</option>
              <option value="langganan">Langganan Cloud SaaS</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Tier Paket</label>
            <select
              v-model="createForm.tier_paket"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="standar">Standar (LMS + CBT Engine)</option>
              <option value="enterprise">Enterprise (Lengkap + Rapor)</option>
              <option value="lite">Lite</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Domain Terdaftar (Opsional)</label>
          <Input v-model="createForm.domain_terdaftar" placeholder="lms.smkn1aksara.sch.id" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Mulai Berlaku</label>
            <Input v-model="createForm.tanggal_rilis" type="date" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">
              {{ createForm.model_lisensi === 'langganan' ? 'Durasi Masa Aktif (Bulan)' : 'Masa Garansi Bugfix (Bulan)' }}
            </label>
            <Input
              v-if="createForm.model_lisensi === 'langganan'"
              v-model.number="createForm.durasi_bulan"
              type="number"
            />
            <Input
              v-else
              v-model.number="createForm.garansi_bulan"
              type="number"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Nilai Kontrak Pengadaan (IDR)</label>
          <Input v-model.number="createForm.nilai_kontrak" type="number" required />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Kontrak</label>
          <Input v-model="createForm.catatan_kontrak" placeholder="Nomor SPK / Keterangan Pembelian BOS" />
        </div>

        <div class="p-3 rounded-lg bg-emerald-950/40 border border-emerald-500/20 text-xs text-emerald-300">
          <ShieldCheck class="w-4 h-4 inline mr-1 text-emerald-400" />
          Sistem akan menandatangani payload menggunakan <strong>RSA-4096 Private Key</strong> dan menghasilkan serial key otomatis.
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isCreateModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="createForm.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600 font-bold">
            Terbitkan & Tandatangani Lisensi
          </Button>
        </div>
      </form>
    </Modal>

    <!-- Modal Perpanjang Masa Aktif -->
    <Modal :show="isRenewModalOpen" @close="isRenewModalOpen = false" title="Perpanjang Kontrak Langganan" maxWidth="md">
      <form @submit.prevent="submitRenew" class="space-y-4">
        <p class="text-xs text-slate-300">
          Perpanjangan lisensi untuk: <strong>{{ selectedLicense?.klien_sekolah?.nama_sekolah }}</strong>
        </p>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Perpanjang Durasi (Bulan)</label>
          <Input v-model.number="renewForm.perpanjang_bulan" type="number" min="1" required />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Nilai Kontrak Tambahan (IDR)</label>
          <Input v-model.number="renewForm.nilai_kontrak_tambahan" type="number" min="0" />
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isRenewModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="renewForm.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
            Simpan Perpanjangan
          </Button>
        </div>
      </form>
    </Modal>

    <!-- Modal RSA Public Key -->
    <Modal :show="isKeyModalOpen" @close="isKeyModalOpen = false" title="Vendor RSA-4096 Public Key" maxWidth="lg">
      <div class="space-y-3">
        <p class="text-xs text-slate-300">
          Kunci publik ini ditanam pada file config core LMS Klien untuk memverifikasi keabsahan tanda tangan lisensi offline tanpa internet.
        </p>
        <textarea
          :value="publicKey"
          readonly
          rows="8"
          class="w-full font-mono text-[10px] p-3 rounded-lg bg-slate-950 border border-slate-800 text-emerald-400 select-all"
        ></textarea>
        <div class="flex justify-end gap-2 pt-2">
          <Button @click="copyPublicKey" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
            <Copy class="w-3.5 h-3.5 mr-1" />
            {{ copyKeySuccess ? 'Tersalin!' : 'Salin Public Key' }}
          </Button>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>
