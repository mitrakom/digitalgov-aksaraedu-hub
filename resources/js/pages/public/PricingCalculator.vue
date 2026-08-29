<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import PublicLayout from '../../layouts/PublicLayout.vue'
import Button from '../../components/ui/Button.vue'
import Badge from '../../components/ui/Badge.vue'
import Card from '../../components/ui/Card.vue'
import Input from '../../components/ui/Input.vue'
import {
  Calculator,
  Download,
  CheckCircle2,
  FileText,
  Building,
  Users,
  Server,
  Cloud,
  ShieldCheck,
  MessageCircle,
  Sparkles
} from 'lucide-vue-next'
import jsPDF from 'jspdf'

const urlParams = new URLSearchParams(window.location.search)
const initialModel = urlParams.get('model') === 'langganan' ? 'langganan' : 'beli_putus'

const modelLisensi = ref<'beli_putus' | 'langganan'>(initialModel)
const tipeSekolah = ref<'smk' | 'sma' | 'ma' | 'mak' | 'smp' | 'mts'>('smk')
const jumlahSiswa = ref(600)
const tierPaket = ref<'standar' | 'enterprise'>('enterprise')

// Data Formulir untuk Penawaran PDF
const namaSekolah = ref('SMK Negeri 1 Teladan')
const namaPIC = ref('Drs. M. Hidayat, M.Kom')
const nomorWA = ref('081234567890')
const kotaSekolah = ref('Kota Bandung')

const isGeneratingPdf = ref(false)

// Formula Kalkulator Harga
const calculatedPrice = computed(() => {
  if (modelLisensi.value === 'beli_putus') {
    // Beli Putus: Base price + Tier multiplier + Scale factor
    let base = 12000000 // 12 Juta
    if (tierPaket.value === 'enterprise') {
      base = 15000000 // 15 Juta
    }
    if (jumlahSiswa.value > 1000) {
      base += 3000000
    }
    return base
  } else {
    // Langganan SaaS per Tahun
    let baseSaaS = 4000000 // 4 Juta/tahun
    if (tierPaket.value === 'enterprise') {
      baseSaaS = 6500000 // 6.5 Juta/tahun
    }
    if (jumlahSiswa.value > 800) {
      baseSaaS += 2000000
    }
    return baseSaaS
  }
})

const formattedPrice = computed(() => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(calculatedPrice.value)
})

const waConsultationUrl = computed(() => {
  const modelText = modelLisensi.value === 'beli_putus' ? 'Beli Putus On-Premise' : 'Langganan SaaS Cloud'
  const text = `Halo Tim AksaraEdu, saya dari ${namaSekolah.value} (${tipeSekolah.value.toUpperCase()}, ${jumlahSiswa.value} siswa). Kami ingin konsultasi paket ${modelText} ${tierPaket.value.toUpperCase()} dengan estimasi ${formattedPrice.value}.`
  return `https://wa.me/6281234567890?text=${encodeURIComponent(text)}`
})

// Fungsi Generate PDF Surat Penawaran Resmi
const downloadQuotationPdf = () => {
  isGeneratingPdf.value = true

  try {
    const doc = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4',
    })

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })

    const nomorPenawaran = `AKSR-Q/${new Date().getFullYear()}/${tipeSekolah.value.toUpperCase()}/${Math.floor(1000 + Math.random() * 9000)}`

    // Header / Kop Surat
    doc.setFillColor(15, 23, 42) // Slate-900
    doc.rect(0, 0, 210, 30, 'F')

    doc.setTextColor(255, 255, 255)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(16)
    doc.text('AKSAEDU CENTRAL HUB INDONESIA', 20, 14)

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)
    doc.setTextColor(52, 211, 153) // Emerald-400
    doc.text('Master Licensing Authority & Learning Management System Technology', 20, 20)
    doc.setTextColor(203, 213, 225)
    doc.setFontSize(8)
    doc.text('Website: hub.aksaraedu.id | Email: sales@aksaraedu.id | WA: 0812-3456-7890', 20, 25)

    // Document Title
    doc.setTextColor(15, 23, 42)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(13)
    doc.text('SURAT PENAWARAN RESMI & ESTIMASI ANGGARAN LMS', 20, 42)

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)
    doc.setTextColor(100, 116, 139)
    doc.text(`Nomor Surat : ${nomorPenawaran}`, 20, 48)
    doc.text(`Tanggal Terbit : ${today}`, 20, 53)
    doc.text('Sifat : Resmi / Proposal Anggaran Pengadaan', 20, 58)

    // School Destination Info
    doc.setFillColor(248, 250, 252)
    doc.setDrawColor(226, 232, 240)
    doc.roundedRect(20, 63, 170, 26, 3, 3, 'FD')

    doc.setTextColor(15, 23, 42)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text('Ditujukan Kepada:', 25, 70)

    doc.setFont('helvetica', 'normal')
    doc.text(`Satuan Pendidikan : ${namaSekolah.value} (${tipeSekolah.value.toUpperCase()})`, 25, 76)
    doc.text(`PIC / Pengusul     : ${namaPIC.value} (${nomorWA.value})`, 25, 81)
    doc.text(`Wilayah Kota/Kab   : ${kotaSekolah.value}`, 25, 86)

    // Table of Specifications
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(10)
    doc.setTextColor(15, 23, 42)
    doc.text('Rincian Paket Lisensi & Spesifikasi Sistem:', 20, 98)

    // Table Header
    doc.setFillColor(241, 245, 249)
    doc.rect(20, 103, 170, 8, 'F')
    doc.setFontSize(8.5)
    doc.text('No', 23, 108.5)
    doc.text('Komponen Layanan / Fitur', 32, 108.5)
    doc.text('Spesifikasi Terpilih', 125, 108.5)

    // Table Rows
    const specs = [
      ['1', 'Model Lisensi', modelLisensi.value === 'beli_putus' ? 'Beli Putus On-Premise (Hak Pakai Selamanya)' : 'Langganan SaaS Cloud Tahunan'],
      ['2', 'Tier Paket Fitur', tierPaket.value === 'enterprise' ? 'Enterprise (CBT Engine + Rapor + Presensi QR)' : 'Standar (LMS + CBT Engine)'],
      ['3', 'Kapasitas Siswa', `Hingga ${jumlahSiswa.value} Siswa Aktif`],
      ['4', 'Dukungan Kurikulum', 'Kurikulum Merdeka & Kurikulum 2013 Terintegrasi'],
      ['5', 'Keamanan Lisensi', 'RSA-4096 Cryptographic Signature & Serial Key'],
      ['6', 'Dukungan Garansi', modelLisensi.value === 'beli_putus' ? 'Garansi Bugfix 3 Bulan Resmi (SLA 24 Jam)' : 'Garansi Penuh Selama Masa Langganan Aktif'],
      ['7', 'Deployment Mode', modelLisensi.value === 'beli_putus' ? 'Zero Phoning Home (100% Offline-Ready Server)' : 'Cloud High-Availability 99.9% Uptime'],
    ]

    let y = 111
    doc.setFont('helvetica', 'normal')
    specs.forEach(([no, item, spec]) => {
      doc.setDrawColor(241, 245, 249)
      doc.line(20, y + 6, 190, y + 6)
      doc.text(no, 23, y + 4.5)
      doc.text(item, 32, y + 4.5)
      doc.text(spec, 125, y + 4.5)
      y += 6.5
    })

    // Total Price Box
    doc.setFillColor(236, 253, 245) // Emerald-50
    doc.setDrawColor(167, 243, 208)
    doc.roundedRect(20, y + 4, 170, 20, 3, 3, 'FD')

    doc.setTextColor(6, 78, 59)
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(10)
    doc.text('TOTAL ESTIMASI INVESTASI:', 25, y + 13)

    doc.setFontSize(14)
    doc.setTextColor(5, 150, 105) // Emerald-600
    doc.text(formattedPrice.value, 125, y + 14)

    doc.setFontSize(7.5)
    doc.setFont('helvetica', 'italic')
    doc.setTextColor(100, 116, 139)
    doc.text('*Sudah termasuk penerbitan RSA signature, master installer, dan panduan teknis.', 25, y + 20)

    // Terms & Conditions
    y += 30
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(8.5)
    doc.setTextColor(15, 23, 42)
    doc.text('Syarat & Ketentuan Pengadaan:', 20, y)

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(7.5)
    doc.setTextColor(71, 85, 105)
    const terms = [
      '1. Penawaran harga ini berlaku selama 30 (tiga puluh) hari kalender sejak tanggal diterbitkan.',
      '2. Pembayaran dapat disesuaikan dengan termin anggaran BOS / Komite Sekolah.',
      '3. Tim teknis AksaraEdu siap mendampingi proses instalasi awal dan bimbingan teknis operator sekolah.',
    ]
    terms.forEach((term, idx) => {
      doc.text(term, 20, y + 5 + (idx * 4.5))
    })

    // Signature Block
    y += 24
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(8)
    doc.setTextColor(15, 23, 42)
    doc.text('Hormat kami,', 140, y)
    doc.setFont('helvetica', 'bold')
    doc.text('AksaraEdu Central Hub Authority', 140, y + 4.5)

    doc.setFont('helvetica', 'italic')
    doc.setFontSize(7.5)
    doc.setTextColor(5, 150, 105)
    doc.text('[Terverifikasi Digital Cryptographic Hub]', 140, y + 18)

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(8.5)
    doc.setTextColor(15, 23, 42)
    doc.text('Budi Santoso, S.Kom', 140, y + 23)
    doc.setFont('helvetica', 'normal')
    doc.setFontSize(7.5)
    doc.setTextColor(100, 116, 139)
    doc.text('Lead Licensing & Distribution', 140, y + 27)

    // Save File
    const cleanSchool = namaSekolah.value.replace(/[^a-zA-Z0-9]/g, '_')
    doc.save(`Penawaran_AksaraEdu_${cleanSchool}.pdf`)
  } catch (error) {
    console.error('Gagal membuat PDF:', error)
    alert('Terjadi kendala saat generate PDF. Silakan coba kembali.')
  } finally {
    isGeneratingPdf.value = false
  }
}
</script>

<template>
  <PublicLayout>
    <Head title="Kalkulator Harga Lisensi & Unduh Penawaran Resmi PDF - AksaraEdu" />

    <div class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Title & Subtitle -->
        <div class="text-center max-w-3xl mx-auto mb-12">
          <Badge variant="success" size="md" class="mb-3">Transparan & Terjangkau</Badge>
          <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
            Kalkulator Lisensi & Generator Surat Penawaran Resmi
          </h1>
          <p class="mt-3 text-sm sm:text-base text-slate-300">
            Sesuaikan kebutuhan satuan pendidikan Anda, lihat estimasi biaya secara transparan, dan unduh berkas <strong class="text-emerald-400">Surat Penawaran PDF</strong> resmi untuk diajukan ke Kepala Sekolah atau Bendahara BOS.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <!-- Left Col: Interactive Calculator Controls -->
          <div class="lg:col-span-7 space-y-6">
            <Card class="bg-slate-900 border-slate-800 p-6 sm:p-8">
              <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <Calculator class="w-5 h-5 text-emerald-400" />
                <span>Konfigurasi Lisensi Sekolah</span>
              </h2>

              <div class="space-y-6">
                <!-- 1. Model Lisensi Selector -->
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2.5">
                    1. Model Kepemilikan Lisensi
                  </label>
                  <div class="grid grid-cols-2 gap-3">
                    <button
                      type="button"
                      @click="modelLisensi = 'beli_putus'"
                      class="p-4 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between"
                      :class="modelLisensi === 'beli_putus'
                        ? 'bg-emerald-950/40 border-emerald-500 text-white shadow-md'
                        : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:border-slate-600'"
                    >
                      <div class="flex items-center justify-between w-full mb-2">
                        <Server class="w-5 h-5 text-emerald-400" />
                        <span v-if="modelLisensi === 'beli_putus'" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                      </div>
                      <div>
                        <p class="text-sm font-bold text-white">Beli Putus On-Premise</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">1x Bayar Hak Pakai Selamanya</p>
                      </div>
                    </button>

                    <button
                      type="button"
                      @click="modelLisensi = 'langganan'"
                      class="p-4 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between"
                      :class="modelLisensi === 'langganan'
                        ? 'bg-teal-950/40 border-teal-500 text-white shadow-md'
                        : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:border-slate-600'"
                    >
                      <div class="flex items-center justify-between w-full mb-2">
                        <Cloud class="w-5 h-5 text-teal-400" />
                        <span v-if="modelLisensi === 'langganan'" class="w-2 h-2 rounded-full bg-teal-400"></span>
                      </div>
                      <div>
                        <p class="text-sm font-bold text-white">Langganan Cloud SaaS</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Biaya Fleksibel per Tahun</p>
                      </div>
                    </button>
                  </div>
                </div>

                <!-- 2. Jenjang Satuan Pendidikan -->
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2.5">
                    2. Jenjang Satuan Pendidikan
                  </label>
                  <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <button
                      v-for="t in ['smk', 'sma', 'ma', 'mak', 'smp', 'mts']"
                      :key="t"
                      type="button"
                      @click="tipeSekolah = t as any"
                      class="py-2.5 px-2 rounded-lg border text-center text-xs font-bold uppercase transition-all cursor-pointer"
                      :class="tipeSekolah === t
                        ? 'bg-emerald-600 border-emerald-500 text-white shadow-sm'
                        : 'bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-750'"
                    >
                      {{ t }}
                    </button>
                  </div>
                </div>

                <!-- 3. Slider Jumlah Siswa -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-slate-300 uppercase tracking-wider">
                      3. Estimasi Kapasitas Siswa
                    </label>
                    <span class="text-sm font-bold text-emerald-400">{{ jumlahSiswa }} Siswa</span>
                  </div>
                  <input
                    type="range"
                    v-model.number="jumlahSiswa"
                    min="100"
                    max="2500"
                    step="50"
                    class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                  />
                  <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                    <span>100 Siswa</span>
                    <span>1.000 Siswa</span>
                    <span>2.500+ Siswa</span>
                  </div>
                </div>

                <!-- 4. Tier Paket Fitur -->
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2.5">
                    4. Tier Paket Fitur
                  </label>
                  <div class="grid grid-cols-2 gap-3">
                    <button
                      type="button"
                      @click="tierPaket = 'standar'"
                      class="p-3.5 rounded-xl border text-left transition-all cursor-pointer"
                      :class="tierPaket === 'standar'
                        ? 'bg-slate-800 border-emerald-500 text-white'
                        : 'bg-slate-850 border-slate-700 text-slate-400 hover:border-slate-600'"
                    >
                      <p class="text-xs font-bold text-white">Paket Standar</p>
                      <p class="text-[10px] text-slate-400 mt-0.5">LMS + CBT Engine Anti-Lag</p>
                    </button>

                    <button
                      type="button"
                      @click="tierPaket = 'enterprise'"
                      class="p-3.5 rounded-xl border text-left transition-all cursor-pointer"
                      :class="tierPaket === 'enterprise'
                        ? 'bg-slate-800 border-emerald-500 text-white'
                        : 'bg-slate-850 border-slate-700 text-slate-400 hover:border-slate-600'"
                    >
                      <p class="text-xs font-bold text-emerald-400">Paket Enterprise (Lengkap)</p>
                      <p class="text-[10px] text-slate-400 mt-0.5">+ Presensi QR & Leger Rapor</p>
                    </button>
                  </div>
                </div>
              </div>
            </Card>

            <!-- Form Data Sekolah untuk Penawaran PDF -->
            <Card class="bg-slate-900 border-slate-800 p-6 sm:p-8">
              <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <FileText class="w-4 h-4 text-emerald-400" />
                <span>Data Sekolah untuk Surat Penawaran Resmi</span>
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-slate-400 mb-1">Nama Satuan Pendidikan</label>
                  <Input v-model="namaSekolah" placeholder="Contoh: SMK Negeri 1 Bandung" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-400 mb-1">Kabupaten / Kota</label>
                  <Input v-model="kotaSekolah" placeholder="Contoh: Kota Bandung" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-400 mb-1">Nama PIC / Kepala Sekolah</label>
                  <Input v-model="namaPIC" placeholder="Contoh: Drs. M. Hidayat, M.Kom" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-400 mb-1">Nomor WhatsApp PIC</label>
                  <Input v-model="nomorWA" placeholder="Contoh: 081234567890" />
                </div>
              </div>
            </Card>
          </div>

          <!-- Right Col: Price Summary Card & Instant PDF Download -->
          <div class="lg:col-span-5 sticky top-28 space-y-6">
            <Card class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-emerald-500/40 p-6 sm:p-8 shadow-2xl relative overflow-hidden">
              <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estimasi Total Investasi</span>
                <Badge variant="success">Resmi AksaraEdu</Badge>
              </div>

              <!-- Price Display -->
              <div class="my-6">
                <p class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                  {{ formattedPrice }}
                </p>
                <p class="text-xs text-emerald-400 mt-1 font-medium">
                  {{ modelLisensi === 'beli_putus' ? '1x Pembayaran Hak Pakai Selamanya + 3 Bulan Garansi' : 'Per Tahun (Sudah Termasuk Server Cloud & Maintenance)' }}
                </p>
              </div>

              <!-- Included Features Checklist -->
              <div class="space-y-2.5 text-xs text-slate-300 py-4 border-y border-slate-800/80">
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Lisensi Resmi {{ tipeSekolah.toUpperCase() }} (Kapasitas {{ jumlahSiswa }} Siswa)</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Kriptografi RSA-4096 & File <code>aksaraedu.lic</code></span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>CBT Engine Anti-Ngadat & Bank Soal</span>
                </div>
                <div v-if="tierPaket === 'enterprise'" class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>Presensi QR Real-time & Modul Leger e-Rapor</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>{{ modelLisensi === 'beli_putus' ? 'Garansi Bugfix 3 Bulan SLA 24 Jam' : 'Garansi & Support Penuh 1 Tahun' }}</span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-6 space-y-3">
                <Button
                  @click="downloadQuotationPdf"
                  :loading="isGeneratingPdf"
                  variant="primary"
                  size="lg"
                  class="w-full justify-center bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-950/50"
                >
                  <Download class="w-4 h-4 mr-2" />
                  Unduh Surat Penawaran Resmi (PDF)
                </Button>

                <Button
                  as="a"
                  :href="waConsultationUrl"
                  target="_blank"
                  variant="outline"
                  size="md"
                  class="w-full justify-center border-slate-700 hover:border-emerald-500 text-slate-200"
                >
                  <MessageCircle class="w-4 h-4 mr-2 text-emerald-400" />
                  Konsultasikan via WhatsApp
                </Button>
              </div>

              <p class="text-[11px] text-slate-500 text-center mt-4">
                Surat penawaran PDF mencantumkan kop resmi dan rincian spesifikasi siap lampir rapat BOS.
              </p>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
