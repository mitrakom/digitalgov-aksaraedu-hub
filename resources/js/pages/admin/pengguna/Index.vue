<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Card from '../../../components/ui/Card.vue';
import Button from '../../../components/ui/Button.vue';
import Badge from '../../../components/ui/Badge.vue';
import Input from '../../../components/ui/Input.vue';
import Modal from '../../../components/ui/Modal.vue';
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
    Check,
} from 'lucide-vue-next';

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: 'super_admin' | 'sales' | 'support';
    phone: string | null;
    avatar: string | null;
    created_at: string;
}

interface Props {
    users: {
        data: UserItem[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    stats: {
        total: number;
        super_admin: number;
        sales: number;
        support: number;
    };
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();
const currentUserId = (page.props as any).auth?.user?.id;

// Filters
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');

const handleFilter = () => {
    router.get(
        '/admin/pengguna',
        {
            search: search.value,
            role: roleFilter.value,
        },
        { preserveState: true, replace: true },
    );
};

// Create User Modal
const isCreateModalOpen = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    role: 'sales' as 'super_admin' | 'sales' | 'support',
    phone: '',
    password: '',
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    isCreateModalOpen.value = true;
};

const submitCreateUser = () => {
    createForm.post('/admin/pengguna', {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
    });
};

// Edit User Modal
const isEditModalOpen = ref(false);
const selectedUser = ref<UserItem | null>(null);
const editForm = useForm({
    name: '',
    email: '',
    role: 'sales' as 'super_admin' | 'sales' | 'support',
    phone: '',
    password: '',
});

const openEditModal = (user: UserItem) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.phone = user.phone || '';
    editForm.password = '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEditUser = () => {
    if (!selectedUser.value) return;
    editForm.put(`/admin/pengguna/${selectedUser.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedUser.value = null;
        },
    });
};

// Delete User Modal
const isDeleteModalOpen = ref(false);
const userToDelete = ref<UserItem | null>(null);
const deleteForm = useForm({});

const openDeleteModal = (user: UserItem) => {
    userToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const submitDeleteUser = () => {
    if (!userToDelete.value) return;
    deleteForm.delete(`/admin/pengguna/${userToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            userToDelete.value = null;
        },
    });
};

const getWaLink = (phone: string | null) => {
    if (!phone) return '#';
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    const formatted = cleanPhone.startsWith('0')
        ? '62' + cleanPhone.slice(1)
        : cleanPhone;
    return `https://wa.me/${formatted}`;
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Pengguna & Tim Vendor - AksaraEdu HQ" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <UserCog class="h-5 w-5 text-emerald-400" />
                <h1 class="text-base font-bold tracking-tight text-slate-100">
                    Manajemen Tim & Pengguna Vendor
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top Stat Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Tim -->
                <Card class="border-slate-800/80 bg-slate-900/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-400">
                                Total Tim Vendor
                            </p>
                            <h3 class="mt-1 text-2xl font-black text-white">
                                {{ stats.total }}
                            </h3>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700/60 bg-slate-800 text-slate-300"
                        >
                            <UserCheck class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">
                        Semua akun internal HQ
                    </p>
                </Card>

                <!-- Super Admin -->
                <Card class="border-slate-800/80 bg-slate-900/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-400">
                                Super Administrator
                            </p>
                            <h3
                                class="mt-1 text-2xl font-black text-indigo-400"
                            >
                                {{ stats.super_admin }}
                            </h3>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-800/60 bg-indigo-950/60 text-indigo-400"
                        >
                            <ShieldCheck class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">
                        Full control & RSA licensing
                    </p>
                </Card>

                <!-- Sales & Marketing -->
                <Card class="border-slate-800/80 bg-slate-900/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-400">
                                Sales & Marketing
                            </p>
                            <h3 class="mt-1 text-2xl font-black text-amber-400">
                                {{ stats.sales }}
                            </h3>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-800/60 bg-amber-950/60 text-amber-400"
                        >
                            <Users2 class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">
                        CRM leads & demo sandbox
                    </p>
                </Card>

                <!-- Technical Support -->
                <Card class="border-slate-800/80 bg-slate-900/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-400">
                                Technical Support
                            </p>
                            <h3 class="mt-1 text-2xl font-black text-sky-400">
                                {{ stats.support }}
                            </h3>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-sky-800/60 bg-sky-950/60 text-sky-400"
                        >
                            <LifeBuoy class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">
                        Helpdesk SLA & telemetri
                    </p>
                </Card>
            </div>

            <!-- Action & Filter Bar -->
            <div
                class="flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div class="flex flex-1 flex-col gap-2.5 sm:flex-row">
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
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none"
                    >
                        <option value="">Semua Peran (RBAC)</option>
                        <option value="super_admin">Super Administrator</option>
                        <option value="sales">Sales & Marketing</option>
                        <option value="support">Technical Support</option>
                    </select>
                    <Button @click="handleFilter" variant="secondary" size="sm">
                        <Search class="mr-1 h-3.5 w-3.5" /> Filter
                    </Button>
                </div>

                <Button
                    @click="openCreateModal"
                    size="sm"
                    class="shrink-0 bg-emerald-600 text-white hover:bg-emerald-500"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Tambah Anggota Tim
                </Button>
            </div>

            <!-- Users Table Card -->
            <Card class="overflow-hidden border-slate-800/80 bg-slate-900/80">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead
                            class="border-b border-slate-800/80 bg-slate-950/70 text-[11px] font-semibold text-slate-400 uppercase"
                        >
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
                                <td
                                    colspan="5"
                                    class="px-5 py-8 text-center text-slate-500"
                                >
                                    Tidak ada anggota tim yang cocok dengan
                                    kriteria pencarian.
                                </td>
                            </tr>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="transition-colors hover:bg-slate-800/40"
                                :class="
                                    user.id === currentUserId
                                        ? 'bg-emerald-950/10'
                                        : ''
                                "
                            >
                                <!-- Name & Email -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border text-sm font-bold"
                                            :class="
                                                user.role === 'super_admin'
                                                    ? 'border-indigo-700/50 bg-indigo-950/60 text-indigo-300'
                                                    : user.role === 'sales'
                                                      ? 'border-amber-700/50 bg-amber-950/60 text-amber-300'
                                                      : 'border-sky-700/50 bg-sky-950/60 text-sky-300'
                                            "
                                        >
                                            {{
                                                user.name
                                                    ? user.name[0].toUpperCase()
                                                    : 'U'
                                            }}
                                        </div>
                                        <div>
                                            <div
                                                class="flex items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-xs font-semibold text-slate-100"
                                                    >{{ user.name }}</span
                                                >
                                                <span
                                                    v-if="
                                                        user.id ===
                                                        currentUserId
                                                    "
                                                    class="py-0.2 rounded border border-emerald-500/40 bg-emerald-500/20 px-1.5 text-[9px] font-bold text-emerald-400 uppercase"
                                                >
                                                    Anda
                                                </span>
                                            </div>
                                            <div
                                                class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-400"
                                            >
                                                <Mail
                                                    class="h-3 w-3 text-slate-500"
                                                />
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
                                            <ShieldCheck class="mr-1 h-3 w-3" />
                                            Super Administrator
                                        </Badge>
                                        <Badge
                                            v-else-if="user.role === 'sales'"
                                            variant="warning"
                                            class="font-semibold"
                                        >
                                            <Users2 class="mr-1 h-3 w-3" />
                                            Sales & Marketing
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="info"
                                            class="font-semibold"
                                        >
                                            <LifeBuoy class="mr-1 h-3 w-3" />
                                            Technical Support
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
                                    <div
                                        v-if="user.phone"
                                        class="flex items-center gap-2"
                                    >
                                        <a
                                            :href="getWaLink(user.phone)"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 font-mono text-xs text-emerald-400 hover:text-emerald-300 hover:underline"
                                        >
                                            <MessageCircle
                                                class="h-3.5 w-3.5 text-emerald-400"
                                            />
                                            {{ user.phone }}
                                        </a>
                                    </div>
                                    <span
                                        v-else
                                        class="text-xs text-slate-600 italic"
                                        >-</span
                                    >
                                </td>

                                <!-- Registered Date -->
                                <td
                                    class="px-5 py-4 font-mono text-xs text-slate-400"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <Button
                                            @click="openEditModal(user)"
                                            variant="ghost"
                                            size="sm"
                                            title="Edit Akun & Peran"
                                            class="text-slate-300 hover:bg-slate-800 hover:text-white"
                                        >
                                            <Edit class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            v-if="user.id !== currentUserId"
                                            @click="openDeleteModal(user)"
                                            variant="ghost"
                                            size="sm"
                                            title="Hapus Akun Tim"
                                            class="text-rose-400 hover:bg-rose-950/40 hover:text-rose-300"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                        <span
                                            v-else
                                            class="px-2 text-[10px] text-slate-500 italic"
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
                <div
                    v-if="users.links && users.links.length > 3"
                    class="flex items-center justify-between border-t border-slate-800/80 bg-slate-950/40 px-5 py-3 text-xs text-slate-400"
                >
                    <div>
                        Menampilkan
                        <span class="font-semibold text-slate-200">{{
                            users.from || 0
                        }}</span>
                        -
                        <span class="font-semibold text-slate-200">{{
                            users.to || 0
                        }}</span>
                        dari
                        <span class="font-semibold text-slate-200">{{
                            users.total
                        }}</span>
                        anggota tim
                    </div>
                    <div class="flex gap-1">
                        <template v-for="(link, i) in users.links" :key="i">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url)"
                                class="rounded px-2.5 py-1 text-xs transition-colors"
                                :class="
                                    link.active
                                        ? 'bg-emerald-600 font-bold text-white'
                                        : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                                "
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
            <Card
                class="space-y-3 border-slate-800/90 bg-gradient-to-br from-slate-900 to-slate-950 p-5 text-slate-300"
            >
                <div
                    class="flex items-center gap-2 text-xs font-semibold text-slate-100"
                >
                    <ShieldCheck class="h-4 w-4 text-emerald-400" />
                    <span
                        >Panduan Hak Akses Berbasis Peran (*Role-Based Access
                        Control*)</span
                    >
                </div>
                <div
                    class="grid grid-cols-1 gap-3 text-[11px] text-slate-400 md:grid-cols-3"
                >
                    <div
                        class="space-y-1 rounded-xl border border-indigo-900/40 bg-slate-950/60 p-3"
                    >
                        <div
                            class="flex items-center gap-1.5 font-semibold text-indigo-400"
                        >
                            <ShieldCheck class="h-3.5 w-3.5" />
                            <span>Super Administrator</span>
                        </div>
                        <p>
                            Memiliki kontrol absolut terhadap ekosistem:
                            penandatanganan RSA-4096, pencabutan lisensi
                            (*revocation*), reset hardware binding, upload
                            update patch, dan penambahan staf internal.
                        </p>
                    </div>
                    <div
                        class="space-y-1 rounded-xl border border-amber-900/40 bg-slate-950/60 p-3"
                    >
                        <div
                            class="flex items-center gap-1.5 font-semibold text-amber-400"
                        >
                            <Users2 class="h-3.5 w-3.5" />
                            <span>Sales & Marketing</span>
                        </div>
                        <p>
                            Fokus pada ekspansi & konversi: memantau prospek
                            Leads Demo Sandbox, follow-up calon klien sekolah,
                            pencatatan database Sekolah Mitra CRM, dan inisiasi
                            penawaran lisensi.
                        </p>
                    </div>
                    <div
                        class="space-y-1 rounded-xl border border-sky-900/40 bg-slate-950/60 p-3"
                    >
                        <div
                            class="flex items-center gap-1.5 font-semibold text-sky-400"
                        >
                            <LifeBuoy class="h-3.5 w-3.5" />
                            <span>Technical Support</span>
                        </div>
                        <p>
                            Fokus pada stabilitas & kepuasan mitra:
                            menindaklanjuti Tiket Bantuan SLA Garansi, memantau
                            Telemetri Heartbeat, dan mengirimkan siaran
                            pengumuman teknis ke instans sekolah.
                        </p>
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
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Nama Lengkap Anggota Tim *</label
                    >
                    <Input
                        v-model="createForm.name"
                        placeholder="Contoh: Muhammad Ihsan, S.Kom"
                        required
                    />
                    <p
                        v-if="createForm.errors.name"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ createForm.errors.name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-300"
                            >Alamat Email Login *</label
                        >
                        <Input
                            v-model="createForm.email"
                            type="email"
                            placeholder="ihsan@aksaraedu.id"
                            required
                        />
                        <p
                            v-if="createForm.errors.email"
                            class="mt-1 text-[11px] text-rose-400"
                        >
                            {{ createForm.errors.email }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-300"
                            >No. WhatsApp / Kontak</label
                        >
                        <Input
                            v-model="createForm.phone"
                            placeholder="081234567890"
                        />
                        <p
                            v-if="createForm.errors.phone"
                            class="mt-1 text-[11px] text-rose-400"
                        >
                            {{ createForm.errors.phone }}
                        </p>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Pilih Peran (*Role*) *</label
                    >
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <!-- Super Admin -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                createForm.role === 'super_admin'
                                    ? 'border-indigo-500 bg-indigo-950/40 text-indigo-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold"
                                    >Super Admin</span
                                >
                                <input
                                    type="radio"
                                    value="super_admin"
                                    v-model="createForm.role"
                                    class="text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                Akses penuh eksekutif & teknis
                            </p>
                        </label>

                        <!-- Sales -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                createForm.role === 'sales'
                                    ? 'border-amber-500 bg-amber-950/40 text-amber-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold"
                                    >Sales & Mkt</span
                                >
                                <input
                                    type="radio"
                                    value="sales"
                                    v-model="createForm.role"
                                    class="text-amber-600 focus:ring-amber-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                CRM leads, demo & registrasi klien
                            </p>
                        </label>

                        <!-- Support -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                createForm.role === 'support'
                                    ? 'border-sky-500 bg-sky-950/40 text-sky-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold">Support</span>
                                <input
                                    type="radio"
                                    value="support"
                                    v-model="createForm.role"
                                    class="text-sky-600 focus:ring-sky-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                Helpdesk SLA & telemetri
                            </p>
                        </label>
                    </div>
                    <p
                        v-if="createForm.errors.role"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ createForm.errors.role }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Kata Sandi Awal (*Password*) *</label
                    >
                    <Input
                        v-model="createForm.password"
                        type="password"
                        placeholder="Minimal 8 karakter"
                        required
                    />
                    <p
                        v-if="createForm.errors.password"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ createForm.errors.password }}
                    </p>
                </div>
            </form>

            <template #footer>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="isCreateModalOpen = false"
                >
                    Batal
                </Button>
                <Button
                    size="sm"
                    class="bg-emerald-600 text-white hover:bg-emerald-500"
                    :disabled="createForm.processing"
                    @click="submitCreateUser"
                >
                    <Check class="mr-1 h-4 w-4" />
                    {{
                        createForm.processing
                            ? 'Menyimpan...'
                            : 'Simpan Akun Tim'
                    }}
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
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Nama Lengkap Anggota Tim *</label
                    >
                    <Input
                        v-model="editForm.name"
                        placeholder="Nama Lengkap"
                        required
                    />
                    <p
                        v-if="editForm.errors.name"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ editForm.errors.name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-300"
                            >Alamat Email Login *</label
                        >
                        <Input
                            v-model="editForm.email"
                            type="email"
                            placeholder="Email"
                            required
                        />
                        <p
                            v-if="editForm.errors.email"
                            class="mt-1 text-[11px] text-rose-400"
                        >
                            {{ editForm.errors.email }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-300"
                            >No. WhatsApp / Kontak</label
                        >
                        <Input
                            v-model="editForm.phone"
                            placeholder="081234567890"
                        />
                        <p
                            v-if="editForm.errors.phone"
                            class="mt-1 text-[11px] text-rose-400"
                        >
                            {{ editForm.errors.phone }}
                        </p>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Peran (*Role*) *</label
                    >
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <!-- Super Admin -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                editForm.role === 'super_admin'
                                    ? 'border-indigo-500 bg-indigo-950/40 text-indigo-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold"
                                    >Super Admin</span
                                >
                                <input
                                    type="radio"
                                    value="super_admin"
                                    v-model="editForm.role"
                                    class="text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                Akses penuh eksekutif & teknis
                            </p>
                        </label>

                        <!-- Sales -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                editForm.role === 'sales'
                                    ? 'border-amber-500 bg-amber-950/40 text-amber-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold"
                                    >Sales & Mkt</span
                                >
                                <input
                                    type="radio"
                                    value="sales"
                                    v-model="editForm.role"
                                    class="text-amber-600 focus:ring-amber-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                CRM leads, demo & registrasi klien
                            </p>
                        </label>

                        <!-- Support -->
                        <label
                            class="flex cursor-pointer flex-col justify-between rounded-xl border p-3 transition-all"
                            :class="
                                editForm.role === 'support'
                                    ? 'border-sky-500 bg-sky-950/40 text-sky-300 shadow-sm'
                                    : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                            "
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold">Support</span>
                                <input
                                    type="radio"
                                    value="support"
                                    v-model="editForm.role"
                                    class="text-sky-600 focus:ring-sky-500"
                                />
                            </div>
                            <p class="text-[10px] leading-tight text-slate-400">
                                Helpdesk SLA & telemetri
                            </p>
                        </label>
                    </div>
                    <p
                        v-if="editForm.errors.role"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ editForm.errors.role }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-slate-300"
                        >Kata Sandi Baru (*Kosongkan jika tidak diubah*)</label
                    >
                    <Input
                        v-model="editForm.password"
                        type="password"
                        placeholder="Minimal 8 karakter baru"
                    />
                    <p
                        v-if="editForm.errors.password"
                        class="mt-1 text-[11px] text-rose-400"
                    >
                        {{ editForm.errors.password }}
                    </p>
                </div>
            </form>

            <template #footer>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="isEditModalOpen = false"
                >
                    Batal
                </Button>
                <Button
                    size="sm"
                    class="bg-emerald-600 text-white hover:bg-emerald-500"
                    :disabled="editForm.processing"
                    @click="submitEditUser"
                >
                    <Check class="mr-1 h-4 w-4" />
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
                <div
                    class="flex items-center gap-3 rounded-xl border border-rose-800/60 bg-rose-950/40 p-3 text-rose-300"
                >
                    <AlertTriangle class="h-5 w-5 shrink-0 text-rose-400" />
                    <p class="text-xs">
                        Aksi ini akan mencabut akses login akun ini ke Central
                        Hub secara permanen.
                    </p>
                </div>
                <p class="text-xs text-slate-300">
                    Apakah Anda yakin ingin menghapus akun
                    <strong class="text-white">{{ userToDelete?.name }}</strong>
                    ({{ userToDelete?.email }})?
                </p>
            </div>

            <template #footer>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="isDeleteModalOpen = false"
                >
                    Batal
                </Button>
                <Button
                    size="sm"
                    class="bg-rose-600 text-white hover:bg-rose-500"
                    :disabled="deleteForm.processing"
                    @click="submitDeleteUser"
                >
                    <Trash2 class="mr-1 h-4 w-4" />
                    {{
                        deleteForm.processing
                            ? 'Menghapus...'
                            : 'Ya, Hapus Akun'
                    }}
                </Button>
            </template>
        </Modal>
    </AdminLayout>
</template>
