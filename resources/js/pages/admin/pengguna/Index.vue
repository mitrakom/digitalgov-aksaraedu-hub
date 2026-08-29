<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from '../../../components/ui/Card.vue'
import Button from '../../../components/ui/Button.vue'
import Badge from '../../../components/ui/Badge.vue'
import Input from '../../../components/ui/Input.vue'
import Modal from '../../../components/ui/Modal.vue'
import {
  UserCog,
  Plus,
  Search,
  ShieldCheck,
  Users2,
  LifeBuoy,
  Edit,
  Trash2,
  Mail,
  Phone,
  MessageCircle,
  KeyRound,
  AlertTriangle,
  UserCheck,
  Check
} from 'lucide-vue-next'

interface UserItem {
  id: number
  name: string
  email: string
  role: 'super_admin' | 'sales' | 'support'
  phone: string | null
  avatar: string | null
  created_at: string
}

interface Props {
  users: {
    data: UserItem[]
    links: any[]
    total: number
    from: number
    to: number
  }
  stats: {
    total: number
    super_admin: number
    sales: number
    support: number
  }
  filters: {
    search?: string
    role?: string
  }
}

const props = defineProps<Props>()
const page = usePage()
const currentUserId = (page.props as any).auth?.user?.id

// Filters
const search = ref(props.filters.search || '')
const roleFilter = ref(props.filters.role || '')

const handleFilter = () => {
  router.get('/admin/pengguna', {
    search: search.value,
    role: roleFilter.value,
  }, { preserveState: true, replace: true })
}

// Create User Modal
const isCreateModalOpen = ref(false)
const createForm = useForm({
  name: '',
  email: '',
  role: 'sales' as 'super_admin' | 'sales' | 'support',
  phone: '',
  password: '',
})

const openCreateModal = () => {
  createForm.reset()
  createForm.clearErrors()
  isCreateModalOpen.value = true
}

const submitCreateUser = () => {
  createForm.post('/admin/pengguna', {
    onSuccess: () => {
      isCreateModalOpen.value = false
      createForm.reset()
    },
  })
}

// Edit User Modal
const isEditModalOpen = ref(false)
const selectedUser = ref<UserItem | null>(null)
const editForm = useForm({
  name: '',
  email: '',
  role: 'sales' as 'super_admin' | 'sales' | 'support',
  phone: '',
  password: '',
})

const openEditModal = (user: UserItem) => {
  selectedUser.value = user
  editForm.name = user.name
  editForm.email = user.email
  editForm.role = user.role
  editForm.phone = user.phone || ''
  editForm.password = ''
  editForm.clearErrors()
  isEditModalOpen.value = true
}

const submitEditUser = () => {
  if (!selectedUser.value) return
  editForm.put(`/admin/pengguna/${selectedUser.value.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      selectedUser.value = null
    },
  })
}

// Delete User Modal
const isDeleteModalOpen = ref(false)
const userToDelete = ref<UserItem | null>(null)
const deleteForm = useForm({})

const openDeleteModal = (user: UserItem) => {
  userToDelete.value = user
  isDeleteModalOpen.value = true
}

const submitDeleteUser = () => {
  if (!userToDelete.value) return
  deleteForm.delete(`/admin/pengguna/${userToDelete.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
      userToDelete.value = null
    },
  })
}

const getWaLink = (phone: string | null) => {
  if (!phone) return '#'
  const cleanPhone = phone.replace(/[^0-9]/g, '')
  const formatted = cleanPhone.startsWith('0') ? '62' + cleanPhone.slice(1) : cleanPhone
  return `https://wa.me/${formatted}`
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pengguna & Tim Vendor - AksaraEdu HQ" />

    <template #header-title>
      <div class="flex items-center gap-2">
        <UserCog class="w-5 h-5 text-emerald-400" />
        <h1 class="text-base font-bold text-slate-100 tracking-tight">Manajemen Tim & Pengguna Vendor</h1>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Top Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Tim -->
        <Card class="p-4 bg-slate-900/80 border-slate-800/80">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-medium text-slate-400">Total Tim Vendor</p>
              <h3 class="text-2xl font-black text-white mt-1">{{ stats.total }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-slate-300">
              <UserCheck class="w-5 h-5" />
            </div>
          </div>
          <p class="text-[11px] text-slate-500 mt-2">Semua akun internal HQ</p>
        </Card>

        <!-- Super Admin -->
        <Card class="p-4 bg-slate-900/80 border-slate-800/80">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-medium text-slate-400">Super Administrator</p>
              <h3 class="text-2xl font-black text-indigo-400 mt-1">{{ stats.super_admin }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-950/60 border border-indigo-800/60 flex items-center justify-center text-indigo-400">
              <ShieldCheck class="w-5 h-5" />
            </div>
          </div>
          <p class="text-[11px] text-slate-500 mt-2">Full control & RSA licensing</p>
        </Card>

        <!-- Sales & Marketing -->
        <Card class="p-4 bg-slate-900/80 border-slate-800/80">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-medium text-slate-400">Sales & Marketing</p>
              <h3 class="text-2xl font-black text-amber-400 mt-1">{{ stats.sales }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-950/60 border border-amber-800/60 flex items-center justify-center text-amber-400">
              <Users2 class="w-5 h-5" />
            </div>
          </div>
          <p class="text-[11px] text-slate-500 mt-2">CRM leads & demo sandbox</p>
        </Card>

        <!-- Technical Support -->
        <Card class="p-4 bg-slate-900/80 border-slate-800/80">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-medium text-slate-400">Technical Support</p>
              <h3 class="text-2xl font-black text-sky-400 mt-1">{{ stats.support }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-950/60 border border-sky-800/60 flex items-center justify-center text-sky-400">
              <LifeBuoy class="w-5 h-5" />
            </div>
          </div>
          <p class="text-[11px] text-slate-500 mt-2">Helpdesk SLA & telemetri</p>
        </Card>
      </div>

      <!-- Action & Filter Bar -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
          <div class="relative flex-1">
            <Input
              v-model="search"
              placeholder="Cari nama anggota tim, email, no HP..."
              @keyup.enter="handleFilter"
            />
          </div>
          <select
            v-model="roleFilter"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-lg border border-slate-700 bg-slate-900 text-slate-200 focus:outline-none focus:border-emerald-500"
          >
            <option value="">Semua Peran (RBAC)</option>
            <option value="super_admin">Super Administrator</option>
            <option value="sales">Sales & Marketing</option>
            <option value="support">Technical Support</option>
          </select>
          <Button @click="handleFilter" variant="secondary" size="sm">
            <Search class="w-3.5 h-3.5 mr-1" /> Filter
          </Button>
        </div>

        <Button @click="openCreateModal" size="sm" class="shrink-0 bg-emerald-600 hover:bg-emerald-500 text-white">
          <Plus class="w-4 h-4 mr-1.5" /> Tambah Anggota Tim
        </Button>
      </div>

      <!-- Users Table Card -->
      <Card class="bg-slate-900/80 border-slate-800/80 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-950/70 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800/80">
              <tr>
                <th class="px-5 py-3.5">Profil Anggota Tim</th>
                <th class="px-5 py-3.5">Peran & Wewenang</th>
                <th class="px-5 py-3.5">Kontak WhatsApp</th>
                <th class="px-5 py-3.5">Terdaftar Sejak</th>
                <th class="px-5 py-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                  Tidak ada anggota tim yang cocok dengan kriteria pencarian.
                </td>
              </tr>
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="hover:bg-slate-800/40 transition-colors"
                :class="user.id === currentUserId ? 'bg-emerald-950/10' : ''"
              >
                <!-- Name & Email -->
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 border"
                      :class="user.role === 'super_admin'
                        ? 'bg-indigo-950/60 text-indigo-300 border-indigo-700/50'
                        : user.role === 'sales'
                          ? 'bg-amber-950/60 text-amber-300 border-amber-700/50'
                          : 'bg-sky-950/60 text-sky-300 border-sky-700/50'"
                    >
                      {{ user.name ? user.name[0].toUpperCase() : 'U' }}
                    </div>
                    <div>
                      <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-slate-100 text-xs">{{ user.name }}</span>
                        <span
                          v-if="user.id === currentUserId"
                          class="px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-[9px] font-bold uppercase"
                        >
                          Anda
                        </span>
                      </div>
                      <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                        <Mail class="w-3 h-3 text-slate-500" />
                        <span>{{ user.email }}</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Role Badge & Description -->
                <td class="px-5 py-4">
                  <div class="space-y-1">
                    <Badge
                      v-if="user.role === 'super_admin'"
                      variant="purple"
                      class="font-semibold"
                    >
                      <ShieldCheck class="w-3 h-3 mr-1" /> Super Administrator
                    </Badge>
                    <Badge
                      v-else-if="user.role === 'sales'"
                      variant="warning"
                      class="font-semibold"
                    >
                      <Users2 class="w-3 h-3 mr-1" /> Sales & Marketing
                    </Badge>
                    <Badge
                      v-else
                      variant="info"
                      class="font-semibold"
                    >
                      <LifeBuoy class="w-3 h-3 mr-1" /> Technical Support
                    </Badge>
                    <p class="text-[10px] text-slate-500">
                      {{
                        user.role === 'super_admin'
                          ? 'Wewenang Penuh (Licensing, Binary, Tim)'
                          : user.role === 'sales'
                            ? 'Leads CRM, Demo Sandbox, Klien'
                            : 'Helpdesk Tiket SLA, Telemetri Heartbeat'
                      }}
                    </p>
                  </div>
                </td>

                <!-- Phone / WA -->
                <td class="px-5 py-4">
                  <div v-if="user.phone" class="flex items-center gap-2">
                    <a
                      :href="getWaLink(user.phone)"
                      target="_blank"
                      class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 font-mono text-xs hover:underline"
                    >
                      <MessageCircle class="w-3.5 h-3.5 text-emerald-400" />
                      {{ user.phone }}
                    </a>
                  </div>
                  <span v-else class="text-slate-600 text-xs italic">-</span>
                </td>

                <!-- Registered Date -->
                <td class="px-5 py-4 text-slate-400 text-xs font-mono">
                  {{ formatDate(user.created_at) }}
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <Button
                      @click="openEditModal(user)"
                      variant="ghost"
                      size="sm"
                      title="Edit Akun & Peran"
                      class="text-slate-300 hover:text-white hover:bg-slate-800"
                    >
                      <Edit class="w-3.5 h-3.5" />
                    </Button>
                    <Button
                      v-if="user.id !== currentUserId"
                      @click="openDeleteModal(user)"
                      variant="ghost"
                      size="sm"
                      title="Hapus Akun Tim"
                      class="text-rose-400 hover:text-rose-300 hover:bg-rose-950/40"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </Button>
                    <span
                      v-else
                      class="text-[10px] text-slate-500 italic px-2"
                      title="Akun sedang digunakan"
                    >
                      Terkunci
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.links && users.links.length > 3" class="px-5 py-3 border-t border-slate-800/80 bg-slate-950/40 flex items-center justify-between text-xs text-slate-400">
          <div>
            Menampilkan <span class="text-slate-200 font-semibold">{{ users.from || 0 }}</span> - <span class="text-slate-200 font-semibold">{{ users.to || 0 }}</span> dari <span class="text-slate-200 font-semibold">{{ users.total }}</span> anggota tim
          </div>
          <div class="flex gap-1">
            <template v-for="(link, i) in users.links" :key="i">
              <button
                v-if="link.url"
                @click="router.get(link.url)"
                class="px-2.5 py-1 rounded text-xs transition-colors"
                :class="link.active ? 'bg-emerald-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
                v-html="link.label"
              ></button>
              <span
                v-else
                class="px-2.5 py-1 text-xs text-slate-600"
                v-html="link.label"
              ></span>
            </template>
          </div>
        </div>
      </Card>

      <!-- Role Matrix Information Box -->
      <Card class="p-5 bg-gradient-to-br from-slate-900 to-slate-950 border-slate-800/90 text-slate-300 space-y-3">
        <div class="flex items-center gap-2 text-slate-100 font-semibold text-xs">
          <ShieldCheck class="w-4 h-4 text-emerald-400" />
          <span>Panduan Hak Akses Berbasis Peran (*Role-Based Access Control*)</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-400">
          <div class="p-3 rounded-xl bg-slate-950/60 border border-indigo-900/40 space-y-1">
            <div class="flex items-center gap-1.5 text-indigo-400 font-semibold">
              <ShieldCheck class="w-3.5 h-3.5" />
              <span>Super Administrator</span>
            </div>
            <p>Memiliki kontrol absolut terhadap ekosistem: penandatanganan RSA-4096, pencabutan lisensi (*revocation*), reset hardware binding, upload update patch, dan penambahan staf internal.</p>
          </div>
          <div class="p-3 rounded-xl bg-slate-950/60 border border-amber-900/40 space-y-1">
            <div class="flex items-center gap-1.5 text-amber-400 font-semibold">
              <Users2 class="w-3.5 h-3.5" />
              <span>Sales & Marketing</span>
            </div>
            <p>Fokus pada ekspansi & konversi: memantau prospek Leads Demo Sandbox, follow-up calon klien sekolah, pencatatan database Sekolah Mitra CRM, dan inisiasi penawaran lisensi.</p>
          </div>
          <div class="p-3 rounded-xl bg-slate-950/60 border border-sky-900/40 space-y-1">
            <div class="flex items-center gap-1.5 text-sky-400 font-semibold">
              <LifeBuoy class="w-3.5 h-3.5" />
              <span>Technical Support</span>
            </div>
            <p>Fokus pada stabilitas & kepuasan mitra: menindaklanjuti Tiket Bantuan SLA Garansi, memantau Telemetri Heartbeat, dan mengirimkan siaran pengumuman teknis ke instans sekolah.</p>
          </div>
        </div>
      </Card>
    </div>

    <!-- Modal Tambah Anggota Tim -->
    <Modal
      :show="isCreateModalOpen"
      title="Tambah Anggota Tim Vendor Baru"
      maxWidth="lg"
      @close="isCreateModalOpen = false"
    >
      <form @submit.prevent="submitCreateUser" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap Anggota Tim *</label>
          <Input
            v-model="createForm.name"
            placeholder="Contoh: Muhammad Ihsan, S.Kom"
            required
          />
          <p v-if="createForm.errors.name" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.name }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Login *</label>
            <Input
              v-model="createForm.email"
              type="email"
              placeholder="ihsan@aksaraedu.id"
              required
            />
            <p v-if="createForm.errors.email" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.email }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">No. WhatsApp / Kontak</label>
            <Input
              v-model="createForm.phone"
              placeholder="081234567890"
            />
            <p v-if="createForm.errors.phone" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.phone }}</p>
          </div>
        </div>

        <!-- Role Selection -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Peran (*Role*) *</label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <!-- Super Admin -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="createForm.role === 'super_admin'
                ? 'bg-indigo-950/40 border-indigo-500 text-indigo-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Super Admin</span>
                <input
                  type="radio"
                  value="super_admin"
                  v-model="createForm.role"
                  class="text-indigo-600 focus:ring-indigo-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">Akses penuh eksekutif & teknis</p>
            </label>

            <!-- Sales -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="createForm.role === 'sales'
                ? 'bg-amber-950/40 border-amber-500 text-amber-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Sales & Mkt</span>
                <input
                  type="radio"
                  value="sales"
                  v-model="createForm.role"
                  class="text-amber-600 focus:ring-amber-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">CRM leads, demo & registrasi klien</p>
            </label>

            <!-- Support -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="createForm.role === 'support'
                ? 'bg-sky-950/40 border-sky-500 text-sky-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Support</span>
                <input
                  type="radio"
                  value="support"
                  v-model="createForm.role"
                  class="text-sky-600 focus:ring-sky-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">Helpdesk SLA & telemetri</p>
            </label>
          </div>
          <p v-if="createForm.errors.role" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.role }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi Awal (*Password*) *</label>
          <Input
            v-model="createForm.password"
            type="password"
            placeholder="Minimal 8 karakter"
            required
          />
          <p v-if="createForm.errors.password" class="text-rose-400 text-[11px] mt-1">{{ createForm.errors.password }}</p>
        </div>
      </form>

      <template #footer>
        <Button variant="ghost" size="sm" @click="isCreateModalOpen = false">
          Batal
        </Button>
        <Button
          size="sm"
          class="bg-emerald-600 hover:bg-emerald-500 text-white"
          :disabled="createForm.processing"
          @click="submitCreateUser"
        >
          <Check class="w-4 h-4 mr-1" />
          {{ createForm.processing ? 'Menyimpan...' : 'Simpan Akun Tim' }}
        </Button>
      </template>
    </Modal>

    <!-- Modal Edit Anggota Tim -->
    <Modal
      :show="isEditModalOpen"
      :title="`Edit Profil: ${selectedUser?.name || 'Anggota Tim'}`"
      maxWidth="lg"
      @close="isEditModalOpen = false"
    >
      <form @submit.prevent="submitEditUser" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap Anggota Tim *</label>
          <Input
            v-model="editForm.name"
            placeholder="Nama Lengkap"
            required
          />
          <p v-if="editForm.errors.name" class="text-rose-400 text-[11px] mt-1">{{ editForm.errors.name }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Login *</label>
            <Input
              v-model="editForm.email"
              type="email"
              placeholder="Email"
              required
            />
            <p v-if="editForm.errors.email" class="text-rose-400 text-[11px] mt-1">{{ editForm.errors.email }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">No. WhatsApp / Kontak</label>
            <Input
              v-model="editForm.phone"
              placeholder="081234567890"
            />
            <p v-if="editForm.errors.phone" class="text-rose-400 text-[11px] mt-1">{{ editForm.errors.phone }}</p>
          </div>
        </div>

        <!-- Role Selection -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Peran (*Role*) *</label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <!-- Super Admin -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="editForm.role === 'super_admin'
                ? 'bg-indigo-950/40 border-indigo-500 text-indigo-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Super Admin</span>
                <input
                  type="radio"
                  value="super_admin"
                  v-model="editForm.role"
                  class="text-indigo-600 focus:ring-indigo-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">Akses penuh eksekutif & teknis</p>
            </label>

            <!-- Sales -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="editForm.role === 'sales'
                ? 'bg-amber-950/40 border-amber-500 text-amber-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Sales & Mkt</span>
                <input
                  type="radio"
                  value="sales"
                  v-model="editForm.role"
                  class="text-amber-600 focus:ring-amber-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">CRM leads, demo & registrasi klien</p>
            </label>

            <!-- Support -->
            <label
              class="p-3 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
              :class="editForm.role === 'support'
                ? 'bg-sky-950/40 border-sky-500 text-sky-300 shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold">Support</span>
                <input
                  type="radio"
                  value="support"
                  v-model="editForm.role"
                  class="text-sky-600 focus:ring-sky-500"
                />
              </div>
              <p class="text-[10px] text-slate-400 leading-tight">Helpdesk SLA & telemetri</p>
            </label>
          </div>
          <p v-if="editForm.errors.role" class="text-rose-400 text-[11px] mt-1">{{ editForm.errors.role }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi Baru (*Kosongkan jika tidak diubah*)</label>
          <Input
            v-model="editForm.password"
            type="password"
            placeholder="Minimal 8 karakter baru"
          />
          <p v-if="editForm.errors.password" class="text-rose-400 text-[11px] mt-1">{{ editForm.errors.password }}</p>
        </div>
      </form>

      <template #footer>
        <Button variant="ghost" size="sm" @click="isEditModalOpen = false">
          Batal
        </Button>
        <Button
          size="sm"
          class="bg-emerald-600 hover:bg-emerald-500 text-white"
          :disabled="editForm.processing"
          @click="submitEditUser"
        >
          <Check class="w-4 h-4 mr-1" />
          {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Akun' }}
        </Button>
      </template>
    </Modal>

    <!-- Modal Konfirmasi Hapus Akun -->
    <Modal
      :show="isDeleteModalOpen"
      title="Konfirmasi Penghapusan Akun Tim"
      maxWidth="md"
      @close="isDeleteModalOpen = false"
    >
      <div class="space-y-3">
        <div class="flex items-center gap-3 p-3 bg-rose-950/40 border border-rose-800/60 rounded-xl text-rose-300">
          <AlertTriangle class="w-5 h-5 shrink-0 text-rose-400" />
          <p class="text-xs">Aksi ini akan mencabut akses login akun ini ke Central Hub secara permanen.</p>
        </div>
        <p class="text-xs text-slate-300">
          Apakah Anda yakin ingin menghapus akun <strong class="text-white">{{ userToDelete?.name }}</strong> ({{ userToDelete?.email }})?
        </p>
      </div>

      <template #footer>
        <Button variant="ghost" size="sm" @click="isDeleteModalOpen = false">
          Batal
        </Button>
        <Button
          size="sm"
          class="bg-rose-600 hover:bg-rose-500 text-white"
          :disabled="deleteForm.processing"
          @click="submitDeleteUser"
        >
          <Trash2 class="w-4 h-4 mr-1" />
          {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus Akun' }}
        </Button>
      </template>
    </Modal>
  </AdminLayout>
</template>
