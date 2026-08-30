# Dokumentasi AksaraEdu Central Hub & Ekosistem LMS

Selamat datang di repositori dokumentasi resmi **AksaraEdu Central Hub**. Dokumentasi ini menyajikan panduan arsitektur sistem, Standar Operasional Prosedur (SOP) Deployment, SOP Serah Terima (Handover) untuk model **Beli Putus (On-Premise)** maupun **Berlangganan (SaaS)**, serta panduan operasional vendor dan helpdesk.

---

## 📚 Daftar Isi Dokumentasi

| Dokumen                                                                                                                                        | Deskripsi                                                                                | Target Pembaca                         |
| :--------------------------------------------------------------------------------------------------------------------------------------------- | :--------------------------------------------------------------------------------------- | :------------------------------------- |
| **[01 Arsitektur & Lisensi](file:///home/server-uit/mitranet-app/lms/aksaraedu/hub/docs/01_ARSITEKTUR_DAN_LISENSI.md)**                        | Arsitektur Central Hub, Enkripsi Asimetris RSA-4096, Model Lisensi, & Siklus Hidup Klien | Lead Architect, Sysadmin, Tech Lead    |
| **[02 SOP Deployment & Provisioning](file:///home/server-uit/mitranet-app/lms/aksaraedu/hub/docs/02_SOP_DEPLOYMENT_DAN_PROVISIONING.md)**      | Panduan teknis instalasi Central Hub, LMS Beli Putus On-Premise, dan LMS SaaS Cloud      | DevOps, Sysadmin, Implementor          |
| **[03 SOP Serah Terima & Aktivasi](file:///home/server-uit/mitranet-app/lms/aksaraedu/hub/docs/03_SOP_SERAH_TERIMA_DAN_AKTIVASI.md)**          | SOP Handover, Berita Acara Serah Terima (BAST), Garansi Bugfix, dan Alur Aktivasi        | Project Manager, Account Exec, Support |
| **[04 Panduan Operasional Vendor](file:///home/server-uit/mitranet-app/lms/aksaraedu/hub/docs/04_PANDUAN_OPERASIONAL_VENDOR_DAN_HELPDESK.md)** | Penggunaan Master Control Panel, Issue License, Reset Hardware, dan Helpdesk CRM         | Tim Support, Admin Vendor, Sales       |
| **[05 Panduan Integrasi API](file:///home/server-uit/mitranet-app/lms/aksaraedu/hub/docs/05_PANDUAN_INTEGRASI_API_DAN_TELEMETRI.md)**          | Spesifikasi Endpoint Gateway, Format Signature Kriptografis, & Telemetri                 | Software Engineer, Integrator          |

---

## 🏛️ Ringkasan Ekosistem AksaraEdu

![Topologi Ekosistem AksaraEdu](./assets/diagrams/01_ekosistem_dan_arsitektur.svg)

---

## 🛡️ Prinsip Keamanan & Legalitas

1. **Zero Vendor Lock-in untuk Beli Putus**: Klien beli putus memegang kendali penuh atas database siswa dan guru mereka. Sistem tidak melakukan _phoning home_ wajib saat proses belajar/ujian berlangsung.
2. **Kriptografi Asimetris Kelas Militer**: Lisensi ditandatangani menggunakan kunci **RSA 4096-bit** pada Central Hub dan hanya didekripsi/divalidasi menggunakan Public Key pada instans sekolah.
3. **Akuntabilitas Kontrak**: Setiap serah terima wajib dilengkapi Berita Acara Serah Terima (BAST) resmi yang memuat nomor lisensi, NPSN, hardware fingerprint, dan masa berlaku garansi.
