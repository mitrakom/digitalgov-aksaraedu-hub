<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from '../../../components/ui/Card.vue'
import Button from '../../../components/ui/Button.vue'
import Badge from '../../../components/ui/Badge.vue'
import Input from '../../../components/ui/Input.vue'
import Modal from '../../../components/ui/Modal.vue'
import {
  LifeBuoy,
  Plus,
  Search,
  Clock,
  CheckCircle2,
  AlertCircle,
  MessageSquare,
  ShieldCheck
} from 'lucide-vue-next'

interface Props {
  tikets: any
  kliens: any[]
  filters: any
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const priorityFilter = ref(props.filters.prioritas || '')

const isCreateModalOpen = ref(false)
const isResponseModalOpen = ref(false)
const selectedTicket = ref<any>(null)

const createForm = useForm({
  klien_sekolah_id: props.kliens.length > 0 ? props.kliens[0].id : '',
  judul_masalah: '',
  deskripsi_kendala: '',
  kategori: 'bug_sistem',
  prioritas: 'sedang',
  is_garansi_claim: true,
})

const responseForm = useForm({
  status: 'in_progress',
  tanggapan_admin: '',
})

const handleFilter = () => {
  router.get('/admin/tiket', {
    search: search.value,
    status: statusFilter.value,
    prioritas: priorityFilter.value,
  }, { preserveState: true, replace: true })
}

const submitCreateTicket = () => {
  createForm.post('/admin/tiket', {
    onSuccess: () => {
      isCreateModalOpen.value = false
      createForm.reset()
    },
  })
}

const openResponseModal = (t: any) => {
  selectedTicket.value = t
  responseForm.status = t.status
  responseForm.tanggapan_admin = t.tanggapan_admin || ''
  isResponseModalOpen.value = true
}

const submitResponse = () => {
  if (!selectedTicket.value) return
  responseForm.patch(`/admin/tiket/${selectedTicket.value.id}/status`, {
    onSuccess: () => {
      isResponseModalOpen.value = false
    },
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Tiket Bantuan & Pelacakan SLA Garansi - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Tiket Bantuan & Pelacakan SLA Garansi</h1>
    </template>

    <div class="space-y-6">
      <!-- Filter and Top Actions -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
          <div class="relative flex-1">
            <Input
              v-model="search"
              placeholder="Cari nomor tiket, judul masalah, nama sekolah..."
              @keyup.enter="handleFilter"
            />
          </div>
          <select
            v-model="statusFilter"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200"
          >
            <option value="">Semua Status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
          <select
            v-model="priorityFilter"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200"
          >
            <option value="">Semua Prioritas</option>
            <option value="rendah">Rendah</option>
            <option value="sedang">Sedang</option>
            <option value="tinggi">Tinggi</option>
            <option value="kritis">Kritis</option>
          </select>
          <Button @click="handleFilter" variant="secondary" size="sm">
            <Search class="w-3.5 h-3.5 mr-1" /> Filter
          </Button>
        </div>

        <Button @click="isCreateModalOpen = true" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
          <Plus class="w-4 h-4 mr-1.5" /> Buka Tiket Baru
        </Button>
      </div>

      <!-- Ticket Cards List -->
      <div class="grid grid-cols-1 gap-4">
        <Card
          v-for="tiket in tikets.data"
          :key="tiket.id"
          class="bg-slate-900 border-slate-800 p-5 hover:border-slate-700 transition-colors"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
            <div class="flex items-center gap-3">
              <span class="font-mono font-bold text-white text-xs bg-slate-800 px-2.5 py-1 rounded border border-slate-700">
                {{ tiket.nomor_tiket }}
              </span>
              <div>
                <h3 class="text-sm font-bold text-white">{{ tiket.judul_masalah }}</h3>
                <p class="text-[11px] text-emerald-400 font-semibold">{{ tiket.klien_sekolah?.nama_sekolah }}</p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Badge v-if="tiket.is_garansi_claim" variant="success" size="sm" class="flex items-center gap-1">
                <ShieldCheck class="w-3 h-3 text-emerald-400" /> SLA Garansi 24 Jam
              </Badge>
              <Badge :variant="tiket.prioritas === 'kritis' ? 'danger' : (tiket.prioritas === 'tinggi' ? 'warning' : 'default')" size="sm">
                {{ tiket.prioritas }}
              </Badge>
              <Badge :variant="tiket.status === 'open' ? 'danger' : (tiket.status === 'in_progress' ? 'warning' : 'success')" size="sm">
                {{ tiket.status }}
              </Badge>
            </div>
          </div>

          <!-- Description -->
          <div class="my-3 text-xs text-slate-300">
            <p>{{ tiket.deskripsi_kendala }}</p>
          </div>

          <!-- Admin Response if any -->
          <div v-if="tiket.tanggapan_admin" class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-300 mb-3">
            <p class="text-[11px] font-bold text-emerald-400 mb-0.5 flex items-center gap-1">
              <MessageSquare class="w-3 h-3" /> Tanggapan Tim Support:
            </p>
            <p>{{ tiket.tanggapan_admin }}</p>
          </div>

          <!-- Footer Actions -->
          <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px] text-slate-400">
            <div class="flex items-center gap-1">
              <Clock class="w-3.5 h-3.5 text-slate-500" />
              <span>Dibuat: {{ new Date(tiket.created_at).toLocaleDateString('id-ID') }}</span>
              <span v-if="tiket.sla_deadline" class="text-amber-400 ml-2">
                (Deadline SLA: {{ new Date(tiket.sla_deadline).toLocaleTimeString('id-ID') }})
              </span>
            </div>
            <Button @click="openResponseModal(tiket)" variant="secondary" size="sm">
              Tanggapi / Update Status
            </Button>
          </div>
        </Card>

        <div v-if="tikets.data.length === 0" class="py-12 text-center text-xs text-slate-500">
          Tidak ada tiket dukungan yang sesuai filter.
        </div>
      </div>
    </div>

    <!-- Modal Create Ticket -->
    <Modal :show="isCreateModalOpen" @close="isCreateModalOpen = false" title="Buka Tiket Bantuan Satuan Pendidikan" maxWidth="lg">
      <form @submit.prevent="submitCreateTicket" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Sekolah Mitra Pengaju</label>
          <select
            v-model="createForm.klien_sekolah_id"
            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            required
          >
            <option v-for="k in kliens" :key="k.id" :value="k.id">
              {{ k.nama_sekolah }} (NPSN: {{ k.npsn }})
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Kendala / Masalah</label>
          <Input v-model="createForm.judul_masalah" placeholder="Contoh: Kesalahan submit jawaban CBT rombel XII RPL" required />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori Masalah</label>
            <select
              v-model="createForm.kategori"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="bug_sistem">Bug Sistem / Software</option>
              <option value="pertanyaan_fitur">Pertanyaan Modul & Fitur</option>
              <option value="instalasi">Instalasi & Konfigurasi Server</option>
              <option value="darurat">Darurat (Ujian Sedang Berlangsung)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Prioritas</label>
            <select
              v-model="createForm.prioritas"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="rendah">Rendah</option>
              <option value="sedang">Sedang</option>
              <option value="tinggi">Tinggi</option>
              <option value="kritis">Kritis (SLA &lt; 24 Jam)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Rinci Kendala</label>
          <textarea
            v-model="createForm.deskripsi_kendala"
            rows="4"
            class="w-full p-3 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500"
            placeholder="Jelaskan kronologi kendala teknis..."
            required
          ></textarea>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-300">
          <input type="checkbox" v-model="createForm.is_garansi_claim" id="garansi_check" class="rounded border-slate-700 bg-slate-800 text-emerald-500" />
          <label for="garansi_check" class="cursor-pointer">Klaim Garansi Bugfix Resmi (Prioritas SLA 24 Jam)</label>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isCreateModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="createForm.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600 font-bold">
            Buka Tiket
          </Button>
        </div>
      </form>
    </Modal>

    <!-- Modal Response & Status Ticket -->
    <Modal :show="isResponseModalOpen" @close="isResponseModalOpen = false" title="Tanggapi & Update Status Tiket" maxWidth="md">
      <form @submit.prevent="submitResponse" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Status Tiket</label>
          <select
            v-model="responseForm.status"
            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
          >
            <option value="open">Open (Menunggu Penanganan)</option>
            <option value="in_progress">In Progress (Sedang Dikerjakan)</option>
            <option value="resolved">Resolved (Terselesaikan)</option>
            <option value="closed">Closed (Ditutup)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan / Tanggapan Tim Support</label>
          <textarea
            v-model="responseForm.tanggapan_admin"
            rows="4"
            class="w-full p-3 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500"
            placeholder="Tuliskan solusi atau instruksi perbaikan..."
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isResponseModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="responseForm.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
            Simpan Tanggapan
          </Button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>
