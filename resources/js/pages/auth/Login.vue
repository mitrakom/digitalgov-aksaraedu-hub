<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import Button from '../../components/ui/Button.vue'
import Input from '../../components/ui/Input.vue'
import Card from '../../components/ui/Card.vue'
import { Lock, Mail, ShieldCheck, ArrowLeft, KeyRound } from 'lucide-vue-next'

const form = useForm({
  email: 'admin@aksaraedu.id',
  password: 'password123',
  remember: true,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}

const autofill = (email: string) => {
  form.email = email
  form.password = 'password123'
}
</script>

<template>
  <Head title="Login Vendor HQ - AksaraEdu Central Hub" />

  <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white relative overflow-hidden font-sans">
    <!-- Glow Backdrop -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[300px] bg-emerald-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- Back to Landing Link -->
      <Link href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-emerald-400 mb-6 transition-colors">
        <ArrowLeft class="w-3.5 h-3.5" /> Kembali ke Portal Publik
      </Link>

      <!-- Brand Header -->
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 flex items-center justify-center text-white font-extrabold text-2xl mx-auto shadow-lg shadow-emerald-500/20 mb-3">
          A
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Vendor Master Hub</h1>
        <p class="text-xs text-slate-400 mt-1">Unified Licensing Authority & Remote Control</p>
      </div>

      <!-- Login Card -->
      <Card class="bg-slate-900 border-slate-800 p-6 sm:p-8 shadow-2xl">
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1">
              <Mail class="w-3.5 h-3.5 text-slate-400" /> Email Vendor
            </label>
            <Input
              v-model="form.email"
              type="email"
              placeholder="admin@aksaraedu.id"
              :error="form.errors.email"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1">
              <Lock class="w-3.5 h-3.5 text-slate-400" /> Kata Sandi
            </label>
            <Input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              :error="form.errors.password"
              required
            />
          </div>

          <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
              <input
                type="checkbox"
                v-model="form.remember"
                class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500/30"
              />
              <span>Ingat sesi masuk</span>
            </label>
            <span class="text-slate-500 text-[11px] flex items-center gap-1">
              <ShieldCheck class="w-3 h-3 text-emerald-400" /> RSA-4096 Secured
            </span>
          </div>

          <Button
            type="submit"
            :loading="form.processing"
            variant="primary"
            size="lg"
            class="w-full justify-center bg-emerald-500 hover:bg-emerald-600 font-bold mt-2"
          >
            <KeyRound class="w-4 h-4 mr-2" />
            Masuk ke Vendor Portal
          </Button>
        </form>

        <!-- Quick Autofill Demo Accounts -->
        <div class="mt-6 pt-5 border-t border-slate-800/80">
          <p class="text-[11px] font-semibold text-slate-400 mb-2">Akun Demo Cepat (Klik untuk isi):</p>
          <div class="flex flex-wrap gap-1.5">
            <button
              type="button"
              @click="autofill('admin@aksaraedu.id')"
              class="px-2.5 py-1 text-[11px] rounded-md bg-slate-800 hover:bg-slate-750 text-slate-300 border border-slate-700"
            >
              Super Admin
            </button>
            <button
              type="button"
              @click="autofill('sales@aksaraedu.id')"
              class="px-2.5 py-1 text-[11px] rounded-md bg-slate-800 hover:bg-slate-750 text-slate-300 border border-slate-700"
            >
              Sales Lead
            </button>
            <button
              type="button"
              @click="autofill('support@aksaraedu.id')"
              class="px-2.5 py-1 text-[11px] rounded-md bg-slate-800 hover:bg-slate-750 text-slate-300 border border-slate-700"
            >
              DevOps & Support
            </button>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>
