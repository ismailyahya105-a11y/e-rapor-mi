# RAPORTKU — Sistem E-Rapor Madrasah Ibtidaiyah (MI)

Aplikasi pengelolaan data induk siswa, penempatan rombongan belajar, kurikulum madrasah, serta sistem penilaian akademik dan nonakademik berbasis standar Kementerian Agama Republik Indonesia (Kemenag).

---

## 📌 Identitas Proyek

- **Nama Aplikasi**: RAPORTKU
- **Framework**: Laravel 12 (PHP 8.4)
- **Lingkungan Lokal**: Laravel Herd (`http://e-rapor-mi.test`)
- **Database**: SQLite
- **Frontend**: Blade, Tailwind CSS v4, Vite
- **Tema Desain**: Emerald Green & Gold Accent
- **Bahasa**: Bahasa Indonesia
- **Zona Waktu**: `Asia/Jakarta`

---

## 🚀 Fitur Utama

### 1. Autentikasi & Otorisasi Pengguna
- Login terpusat dengan pemisahan peran:
  - **Administrator / Guru**: Akses penuh ke dashboard admin, data master, serta penilaian akademik dan nonakademik.
  - **Wali Siswa**: Akses ke Portal Wali yang terisolasi ketat di sisi server (hanya dapat melihat data anak kandung).

### 2. Manajemen Data Master (Tahap 3)
- Tahun Ajaran & Semester (Ganjil/Genap) dengan penjagaan 1 semester aktif tunggal.
- Kelas Rombel (Tingkat 1 s/d 6).
- Data Siswa (NIS unik berformat teks menjaga leading zero, NISN opsional, biodata).
- Penempatan Kelas Siswa per semester dengan riwayat historis tanpa saling menimpa.
- Master Mata Pelajaran (Kode unik, urutan tampilan rapor, status aktif).
- Penugasan Mata Pelajaran ke Kelas dan Semester.
- Manajemen Akun Wali & Pemetaan Siswa ke Akun Wali (1 wali dapat memantau beberapa anak).

### 3. Sistem Penilaian Akademik Siswa (Tahap 4)
- **Pengaturan Komponen Penilaian** (`/admin/pengaturan-penilaian`): Atur bobot per mapel & semester dengan validasi 100% dan preset 1-klik.
- **Input Penilaian Akademik Massal** (`/admin/penilaian-akademik`): Pembedaan tegas nilai kosong (`NULL`) vs angka `0`, live client-side preview, dan saran deskripsi capaian pembelajaran otomatis.
- **Rekap Penilaian Akademik** (`/admin/rekap-nilai`): 6 kartu metrik riil, predikat (A, B, C, D), dan layout siap cetak.

### 4. Sistem Penilaian Nonakademik Siswa (Tahap 5)
- **Penilaian Sikap dan Perilaku** (`/admin/sikap`): Penilaian 7 aspek sikap (Kedisiplinan, Tanggung Jawab, Sopan Santun, Kejujuran, Kerja Sama, Kemandirian, Kepedulian), rating (Sangat Baik, Baik, Cukup, Perlu Bimbingan), catatan perkembangan, dan rangkuman sikap umum.
- **Kehadiran Siswa** (`/admin/kehadiran`): Presensi harian massal dan rekapitulasi kehadiran semester (Hadir, Sakit, Izin, Alpa) dengan pencegahan data tanggal ganda.
- **Keaktifan Belajar Siswa** (`/admin/keaktifan`): Dokumentasi pengamatan konkret keaktifan bertanya, diskusi, kegiatan kelas, dan inisiatif belajar.
- **Ekstrakurikuler** (`/admin/ekstrakurikuler`): Master kegiatan ekstrakurikuler madrasah dan pencatatan capaian keikutsertaan siswa per semester.
- **Prestasi Siswa** (`/admin/prestasi`): Pencatatan kejuaraan santri dari tingkat madrasah s.d. internasional.
- **Catatan Wali Kelas** (`/admin/catatan-wali-kelas`): Evaluasi komprehensif 6 dimensi perkembangan belajar, adab, kedisiplinan, potensi, perbaikan, dan saran kepada orang tua.
- **Rekapitulasi Nonakademik** (`/admin/rekap-nonakademik`): Halaman rekapitulasi seluruh aspek nonakademik kelas dan lembar portofolio detail per siswa (`/admin/rekap-nonakademik/{student}`).

---

## 🔑 Akun Demo Pengujian

| Peran | Email | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@raportku.test` | `password` | `http://e-rapor-mi.test/admin` |
| **Wali Siswa (2 Anak)** | `wali@raportku.test` | `password` | `http://e-rapor-mi.test/wali` |
| **Wali Siswa (1 Anak)** | `wali.rizky@raportku.test` | `password` | `http://e-rapor-mi.test/wali` |
| **Wali Baru (0 Anak)** | `wali.baru@raportku.test` | `password` | `http://e-rapor-mi.test/wali` |

---

## 💻 Panduan Pengujian & Build Lokal

```bash
# Menjalankan Seluruh Pengujian (Pest) — 62 Tests
php vendor/bin/pest

# Menjalankan Pengujian Fitur Nonakademik
php vendor/bin/pest tests/Feature/NonAcademicAssessmentTest.php

# Menjalankan Pemformat Kode (Laravel Pint)
php vendor/bin/pint --format agent

# Melakukan Kompilasi Aset Frontend (Vite)
npm run build
```

---

## 📁 Struktur Dokumentasi

- [`docs/PRD.md`](docs/PRD.md) — Product Requirement Document & Spesifikasi Sistem.
- [`docs/PROGRESS.md`](docs/PROGRESS.md) — Catatan Kemajuan dan Riwayat Pengujian Setiap Tahap.
