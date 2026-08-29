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
  Radio,
  Plus,
  Trash2,
  Bell,
  Power,
  Calendar,
  AlertTriangle,
  Info
} from 'lucide-vue-next'

interface Props {
  pengumumans: any
}

defineProps<Props>()

const isModalOpen = ref(false)

const form = useForm({
  judul: '',
  pesan: '',
  tipe: 'info',
  target_model: 'semua',
  is_active: true,
})

const submitBroadcast = () => {
  form.post('/admin/pengumuman', {
    onSuccess: () => {
      isModalOpen.value = false
      form.reset()
    },
  })
}

const toggleStatus = (id: string) => {
  router.patch(`/admin/pengumuman/${id}/toggle`)
}

const deleteAnnouncement = (id: string) => {
  if (confirm('Hapus siaran pengumuman remote ini?')) {
    router.delete(`/admin/pengumuman/${id}`)
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Siaran Remote & Notifikasi Klien - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Siaran Remote & Notifikasi Klien</h1>
    </template>

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400">
          Kirimkan banner pengumuman pemeliharaan atau info rilis dari Central Hub ke Dashboard Admin LMS sekolah mitra.
        </p>
        <Button @click="isModalOpen = true" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
          <Plus class="w-4 h-4 mr-1.5" /> Buat Siaran Baru
        </Button>
      </div>

      <!-- Broadcast Cards List -->
      <div class="grid grid-cols-1 gap-4">
        <Card
          v-for="p in pengumumans.data"
          :key="p.id"
          class="bg-slate-900 border-slate-800 p-5"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center font-bold"
                :class="p.tipe === 'urgent' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : (p.tipe === 'warning' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-sky-500/20 text-sky-400 border border-sky-500/30')"
              >
                <Bell class="w-5 h-5" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-bold text-white">{{ p.judul }}</h3>
                  <Badge :variant="p.tipe === 'urgent' ? 'danger' : (p.tipe === 'warning' ? 'warning' : 'info')" size="sm">
                    {{ p.tipe }}
                  </Badge>
                  <Badge variant="outline" size="sm">Target: {{ p.target_model }}</Badge>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                  Diterbitkan: {{ new Date(p.created_at).toLocaleDateString('id-ID') }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="toggleStatus(p.id)"
                class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors cursor-pointer"
                :class="p.is_active ? 'bg-emerald-950/60 border-emerald-500/40 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-400'"
              >
                {{ p.is_active ? 'Siaran Aktif' : 'Nonaktif' }}
              </button>
              <Button @click="deleteAnnouncement(p.id)" variant="ghost" size="sm" class="text-rose-400 hover:text-rose-300">
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </div>

          <div class="mt-3 text-xs text-slate-300">
            <p>{{ p.pesan }}</p>
          </div>
        </Card>

        <div v-if="pengumumans.data.length === 0" class="py-12 text-center text-xs text-slate-500">
          Belum ada siaran pengumuman remote yang dibuat.
        </div>
      </div>
    </div>

    <!-- Modal Create Broadcast -->
    <Modal :show="isModalOpen" @close="isModalOpen = false" title="Buat Siaran Pengumuman Remote" maxWidth="md">
      <form @submit.prevent="submitBroadcast" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Pengumuman</label>
          <Input v-model="form.judul" placeholder="Contoh: Rilis Pembaruan Patch v1.0.2 Tersedia" required />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe / Urgensi</label>
            <select
              v-model="form.tipe"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="info">Info Standar</option>
              <option value="warning">Peringatan / Maintenance</option>
              <option value="urgent">Kritis / Darurat</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Target Model Klien</label>
            <select
              v-model="form.target_model"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="semua">Semua Instans</option>
              <option value="beli_putus">Beli Putus Saja</option>
              <option value="langganan">Langganan SaaS Saja</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Isi Pesan Siaran</label>
          <textarea
            v-model="form.pesan"
            rows="4"
            class="w-full p-3 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500"
            placeholder="Tuliskan pesan lengkap yang akan tampil di dashboard sekolah..."
            required
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="form.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600 font-bold">
            Publikasikan Siaran
          </Button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>
