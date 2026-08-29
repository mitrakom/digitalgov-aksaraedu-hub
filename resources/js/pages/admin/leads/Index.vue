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
  Users2,
  Search,
  MessageCircle,
  Clock,
  Phone,
  Mail,
  School,
  ExternalLink,
  Edit
} from 'lucide-vue-next'

interface Props {
  leads: any
  filters: any
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status_followup || '')
const isStatusModalOpen = ref(false)
const selectedLead = ref<any>(null)

const statusForm = useForm({
  status_followup: 'baru',
  catatan_sales: '',
})

const handleFilter = () => {
  router.get('/admin/leads', {
    search: search.value,
    status_followup: statusFilter.value,
  }, { preserveState: true, replace: true })
}

const openStatusModal = (lead: any) => {
  selectedLead.value = lead
  statusForm.status_followup = lead.status_followup
  statusForm.catatan_sales = lead.catatan_sales || ''
  isStatusModalOpen.value = true
}

const submitStatusUpdate = () => {
  if (!selectedLead.value) return
  statusForm.patch(`/admin/leads/${selectedLead.value.id}/status`, {
    onSuccess: () => {
      isStatusModalOpen.value = false
    },
  })
}

const getWaUrl = (lead: any) => {
  const text = `Halo Bapak/Ibu ${lead.nama_pemohon}, kami dari AksaraEdu Central Hub. Terima kasih telah mencoba demo sandbox AksaraEdu untuk ${lead.nama_sekolah}. Apakah ada pertanyaan atau modul yang ingin kami presentasikan?`
  return `https://wa.me/${lead.nomor_wa.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(text)}`
}
</script>

<template>
  <AdminLayout>
    <Head title="Leads Demo & Pipeline Penjualan (CRM) - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Leads Demo & Pipeline Penjualan (CRM)</h1>
    </template>

    <div class="space-y-6">
      <!-- Filter Bar -->
      <div class="flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1">
          <Input
            v-model="search"
            placeholder="Cari pemohon, sekolah, no WA, email..."
            @keyup.enter="handleFilter"
          />
        </div>
        <select
          v-model="statusFilter"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200"
        >
          <option value="">Semua Tahapan Sales</option>
          <option value="baru">Baru (Belum Dihubungi)</option>
          <option value="dihubungi">Sudah Dihubungi</option>
          <option value="presentasi">Tahap Presentasi / Demo</option>
          <option value="deal">Deal (Penerbitan Lisensi)</option>
          <option value="lost">Lost</option>
        </select>
        <Button @click="handleFilter" variant="secondary" size="sm">
          <Search class="w-3.5 h-3.5 mr-1" /> Filter
        </Button>
      </div>

      <!-- Leads Grid / Table -->
      <Card class="bg-slate-900 border-slate-800">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Sekolah / Pemohon</th>
                <th class="py-3 px-4">Kontak (WA & Email)</th>
                <th class="py-3 px-4">Jenjang & Siswa</th>
                <th class="py-3 px-4">Minat Model</th>
                <th class="py-3 px-4">Status Follow-Up</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
              <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4">
                  <p class="font-bold text-white">{{ lead.nama_sekolah }}</p>
                  <p class="text-[11px] text-emerald-400 font-medium mt-0.5">{{ lead.nama_pemohon }}</p>
                </td>
                <td class="py-3 px-4">
                  <p class="font-mono text-slate-200">{{ lead.nomor_wa }}</p>
                  <p class="text-[11px] text-slate-400 truncate max-w-[140px]">{{ lead.email }}</p>
                </td>
                <td class="py-3 px-4">
                  <Badge variant="outline" size="sm" class="uppercase font-bold mb-1">{{ lead.tipe_sekolah }}</Badge>
                  <p class="text-[11px] text-slate-400">{{ lead.estimasi_siswa }} Siswa</p>
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="lead.model_minat === 'beli_putus' ? 'success' : 'info'" size="sm">
                    {{ lead.model_minat === 'beli_putus' ? 'Beli Putus' : (lead.model_minat === 'langganan' ? 'SaaS' : 'Konsultasi') }}
                  </Badge>
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="lead.status_followup === 'baru' ? 'info' : (lead.status_followup === 'deal' ? 'success' : (lead.status_followup === 'lost' ? 'danger' : 'warning'))">
                    {{ lead.status_followup }}
                  </Badge>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a
                      :href="getWaUrl(lead)"
                      target="_blank"
                      title="Follow up WhatsApp"
                      class="p-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 transition-colors"
                    >
                      <MessageCircle class="w-3.5 h-3.5" />
                    </a>
                    <button
                      @click="openStatusModal(lead)"
                      title="Ubah Status Follow-Up"
                      class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors cursor-pointer"
                    >
                      <Edit class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="leads.data.length === 0">
                <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                  Tidak ada data leads demo yang cocok.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>

    <!-- Modal Status Followup -->
    <Modal :show="isStatusModalOpen" @close="isStatusModalOpen = false" title="Update Pipeline Status Follow-Up" maxWidth="md">
      <form @submit.prevent="submitStatusUpdate" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Tahapan Follow-Up</label>
          <select
            v-model="statusForm.status_followup"
            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
          >
            <option value="baru">Baru</option>
            <option value="dihubungi">Sudah Dihubungi via WA / Telepon</option>
            <option value="presentasi">Tahap Presentasi / Zoom Demo</option>
            <option value="deal">Deal (Closing Kontrak)</option>
            <option value="lost">Lost (Tidak Berminat / Anggaran Belum Siap)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Sales / CRM</label>
          <textarea
            v-model="statusForm.catatan_sales"
            rows="4"
            class="w-full p-3 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500"
            placeholder="Tuliskan hasil follow-up..."
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isStatusModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="statusForm.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
            Simpan Status
          </Button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>
