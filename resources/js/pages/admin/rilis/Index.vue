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
  Package,
  Plus,
  ShieldCheck,
  Download,
  Trash2,
  CheckCircle2,
  FileCode2,
  Calendar
} from 'lucide-vue-next'

interface Props {
  releases: any
}

defineProps<Props>()

const isModalOpen = ref(false)

const form = useForm({
  nomor_versi: '1.0.2',
  tipe_rilis: 'patch_bugfix',
  ringkasan_perubahan: '• Perbaikan kestabilan koneksi ujian CBT massal\n• Optimasi memori render modul raport Kurikulum Merdeka\n• Peningkatan sanitasi input keamanan',
  minimal_versi_lms: '1.0.0',
  is_public: true,
  is_critical_patch: true,
  checksum_sha256: '',
})

const submitRelease = () => {
  form.post('/admin/rilis', {
    onSuccess: () => {
      isModalOpen.value = false
      form.reset()
    },
  })
}

const deleteRelease = (id: string, version: string) => {
  if (confirm(`Hapus paket rilis v${version} dari registry pusat?`)) {
    router.delete(`/admin/rilis/${id}`)
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Repositori Rilis & Pembaruan (OTA Registry) - AksaraEdu HQ" />

    <template #header-title>
      <h1 class="text-base font-bold text-slate-100 tracking-tight">Repositori Rilis & Pembaruan (OTA Registry)</h1>
    </template>

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400">
          Kelola paket rilis pembaruan, patch bugfix resmi, dan changelog terdistribusi ke seluruh klien.
        </p>
        <Button @click="isModalOpen = true" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600">
          <Plus class="w-4 h-4 mr-1.5" /> Publikasikan Rilis Baru
        </Button>
      </div>

      <!-- Release List Grid -->
      <div class="grid grid-cols-1 gap-4">
        <Card
          v-for="rel in releases.data"
          :key="rel.id"
          class="bg-slate-900 border-slate-800 p-6"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-mono font-bold">
                v{{ rel.nomor_versi }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-bold text-white">Rilis Versi {{ rel.nomor_versi }}</h3>
                  <Badge :variant="rel.tipe_rilis === 'patch_bugfix' ? 'success' : 'info'" size="sm">
                    {{ rel.tipe_rilis }}
                  </Badge>
                  <Badge v-if="rel.is_critical_patch" variant="warning" size="sm">Critical Security Patch</Badge>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                  Kompatibel mulai versi: <span class="font-mono text-emerald-400">v{{ rel.minimal_versi_lms }}</span> |
                  Didownload: {{ rel.riwayat_updates_count || 0 }} kali
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-400 flex items-center gap-1">
                <Calendar class="w-3.5 h-3.5 text-slate-500" />
                {{ rel.published_at ? new Date(rel.published_at).toLocaleDateString('id-ID') : '-' }}
              </span>
              <Button @click="deleteRelease(rel.id, rel.nomor_versi)" variant="ghost" size="sm" class="text-rose-400 hover:text-rose-300">
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </div>

          <!-- Changelog -->
          <div class="mt-4">
            <h4 class="text-xs font-semibold text-slate-300 mb-1.5">Catatan Perubahan (Changelog):</h4>
            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs text-slate-300 font-mono whitespace-pre-line leading-relaxed">
              {{ rel.ringkasan_perubahan }}
            </div>
          </div>

          <!-- Security Checksum Strip -->
          <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-[11px] text-slate-400 font-mono">
            <span class="truncate max-w-xl">SHA-256: {{ rel.checksum_sha256 || 'Generated Automatically' }}</span>
            <span class="text-emerald-400 flex items-center gap-1"><ShieldCheck class="w-3.5 h-3.5" /> RSA Signed Integrity</span>
          </div>
        </Card>

        <div v-if="releases.data.length === 0" class="py-12 text-center text-xs text-slate-500">
          Belum ada rilis versi software di repository pusat.
        </div>
      </div>
    </div>

    <!-- Modal Publish Release -->
    <Modal :show="isModalOpen" @close="isModalOpen = false" title="Publikasikan Rilis Pembaruan Baru" maxWidth="lg">
      <form @submit.prevent="submitRelease" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Versi (Semver)</label>
            <Input v-model="form.nomor_versi" placeholder="1.0.2" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe Rilis</label>
            <select
              v-model="form.tipe_rilis"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100"
            >
              <option value="patch_bugfix">Patch Bugfix</option>
              <option value="minor_feature">Minor Feature</option>
              <option value="major_curriculum">Major Curriculum Upgrade</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Minimal Versi LMS Terpasang</label>
          <Input v-model="form.minimal_versi_lms" placeholder="1.0.0" required />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Ringkasan Perubahan (Changelog)</label>
          <textarea
            v-model="form.ringkasan_perubahan"
            rows="5"
            class="w-full p-3 text-xs rounded-lg border border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500"
            placeholder="Tuliskan daftar poin perubahan dan perbaikan..."
            required
          ></textarea>
        </div>

        <div class="flex items-center gap-4 text-xs text-slate-300 pt-1">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_critical_patch" class="rounded border-slate-700 bg-slate-800 text-emerald-500" />
            <span>Patch Kritis Darurat</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_public" class="rounded border-slate-700 bg-slate-800 text-emerald-500" />
            <span>Tampilkan di Registry Publik</span>
          </label>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
          <Button @click="isModalOpen = false" variant="ghost" size="sm">Batal</Button>
          <Button type="submit" :loading="form.processing" variant="primary" size="sm" class="bg-emerald-500 hover:bg-emerald-600 font-bold">
            Publikasikan ke Registry
          </Button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>
