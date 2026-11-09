# PRD — RAPORTKU (Sistem E-Rapor Madrasah Ibtidaiyah)

## 1. Identitas & Visi Produk
- **Nama Aplikasi**: RAPORTKU
- **Deskripsi**: Sistem Informasi Pengelolaan Data Akademik, Penilaian, dan Buku Rapor Digital untuk Madrasah Ibtidaiyah (MI).
- **Target Pengguna**:
  1. **Administrator / Guru**: Mengelola master data akademik, kurikulum, penempatan kelas, penugasan mata pelajaran, bobot komponen asesmen, input nilai akademik massal, serta seluruh aspek penilaian nonakademik santri/siswa.
  2. **Wali Siswa**: Memantau identitas anak, rombongan belajar, dan hasil capaian belajar melalui portal terisolasi dan aman.
- **Teknologi**:
  - Backend: Laravel 12 (PHP 8.4)
  - Database: SQLite (dukungan penuh ACID & foreign key constraints)
  - Frontend: Blade Templating, Tailwind CSS v4, Vanilla JS untuk reactive client-side calculation
  - Lingkungan: Laravel Herd (`http://e-rapor-mi.test`), Zona Waktu `Asia/Jakarta`

---

## 2. Arsitektur & Desain Database (Tahap 1 - Tahap 5)

### 2.1 Entitas Master Data (Tahap 1 - 3)
- `users`: Autentikasi dan peran (`admin`, `wali`).
- `academic_years`: Tahun ajaran (misal 2025/2026).
- `semesters`: Semester (Ganjil/Genap), status aktif tunggal (`is_active`).
- `school_classes`: Kelas rombel (Tingkat 1-6, e.g. 4A, 4B).
- `students`: Data induk siswa (NIS leading zero, NISN opsional, biodata).
- `enrollments`: Penempatan siswa pada kelas per semester (menjaga riwayat historis).
- `subjects`: Master mata pelajaran standar Kemenag & muatan lokal.
- `subject_assignments`: Pemetaan mata pelajaran ke kelas dan semester.
- `guardian_students`: Relasi many-to-many akun wali dengan siswa.

### 2.2 Entitas Penilaian Akademik (Tahap 4)
- `assessment_components`: Komponen bobot persentase penilaian per mata pelajaran dan semester (total harus 100%).
- `student_assessment_scores`: Nilai mentah siswa per komponen (nullable, empty $\neq 0$).
- `student_subject_results`: Nilai akhir berbobot, deskripsi capaian pembelajaran, dan status kelengkapan.

### 2.3 Entitas Penilaian Nonakademik (Tahap 5)
- `attitude_aspects`: Aspek sikap yang dapat dikonfigurasi (Kedisiplinan, Tanggung Jawab, Sopan Santun, Kejujuran, Kerja Sama, Kemandirian, Kepedulian).
- `student_attitude_ratings`: Nilai sikap siswa per aspek dan semester (Sangat Baik, Baik, Cukup, Perlu Bimbingan) + catatan perkembangan.
- `student_attitude_summaries`: Catatan rangkuman perkembangan karakter santri/siswa per semester.
- `student_attendances`: Presensi siswa harian per kelas, tanggal, dan semester (Hadir, Sakit, Izin, Alpa) dengan unique constraint `[student_id, date, semester_id]` untuk mencegah data ganda.
- `student_activities`: Observasi keaktifan belajar siswa (aspek, deskripsi pengamatan konkret, catatan guru).
- `extracurriculars`: Master kegiatan ekstrakurikuler madrasah (Pramuka, Tilawah, Futsal, Kaligrafi, UKS, dll.).
- `student_extracurriculars`: Catatan keikutsertaan ekskul siswa per semester (predikat dan deskripsi capaian).
- `student_achievements`: Dokumentasi prestasi dan kejuaraan siswa (tingkat madrasah s.d. internasional, peringkat, penyelenggara, tanggal).
- `homeroom_notes`: Lembar evaluasi komprehensif wali kelas (perkembangan belajar, sikap perilaku, kedisiplinan, potensi, hal yang perlu ditingkatkan, saran orang tua).

---

## 3. Fitur Penilaian Akademik (Tahap 4)
- Pengaturan komponen penilaian dengan validasi total 100% dan preset 1-klik.
- Input nilai siswa massal dengan pembedaan tegas string kosong (`NULL`) vs angka `0`.
- Perhitungan nilai akhir berbobot secara otomatis di server.
- Rekapitulasi nilai akademik dengan 6 kartu metrik riil dari database.

---

## 4. Fitur Penilaian Nonakademik (Tahap 5)

### 4.1 Penilaian Sikap dan Perilaku (`/admin/sikap`)
- Penilaian 7 aspek sikap standar madrasah (dapat ditambah/diedit).
- Pilihan predikat: Sangat Baik, Baik, Cukup, Perlu Bimbingan.
- Catatan perkembangan per aspek serta catatan rangkuman sikap umum.
- Disimpan transaksional dan terhubung per semester.

### 4.2 Rekap Kehadiran Siswa (`/admin/kehadiran`)
- Dua mode: **Presensi Harian Massal** dan **Rekap Kehadiran Semester**.
- 4 Status Kehadiran: Hadir (H), Sakit (S), Izin (I), Alpa (A).
- Pencegahan duplikasi data: Update otomatis jika tanggal yang sama diinput ulang.
- Rekap semester dihitung riil dari database. Menampilkan status *"Belum tersedia"* jika belum ada data (bukan angka 0 palsu).

### 4.3 Keaktifan Belajar Siswa (`/admin/keaktifan`)
- Dokumentasi keterlibatan aktif siswa dalam pembelajaran.
- Aspek standar: Keaktifan Bertanya, Partisipasi Diskusi, Keterlibatan Kegiatan Kelas, Kerja Sama Kelompok, Inisiatif Belajar.
- Catatan deskripsi pengamatan nyata dan apresiasi/arahan guru.

### 4.4 Ekstrakurikuler (`/admin/ekstrakurikuler`)
- Master kegiatan bakat minat santri (Keagamaan, Kepanduan, Olahraga, Seni & Budaya, dll.).
- Pencatatan keikutsertaan siswa per semester dengan predikat dan deskripsi perkembangan.
- Satu siswa dapat mengikuti lebih dari satu kegiatan ekstrakurikuler.

### 4.5 Prestasi Siswa (`/admin/prestasi`)
- Pencatatan prestasi dan kejuaraan siswa bersifat opsional.
- Kategori tingkat: Madrasah, Kecamatan, Kabupaten/Kota, Provinsi, Nasional, Internasional.
- Detail peringkat/juara, penyelenggara, tanggal, dan catatan kegiatan.

### 4.6 Catatan Wali Kelas (`/admin/catatan-wali-kelas`)
- Form evaluasi perkembangan siswa mencakup 6 dimensi:
  1. Perkembangan Belajar
  2. Sikap dan Perilaku
  3. Kedisiplinan
  4. Potensi Siswa
  5. Hal yang Perlu Ditingkatkan
  6. Saran Kepada Orang Tua
- Tombol bantuan cepat draf catatan inspiratif dan mendidik.

### 4.7 Rekapitulasi Nonakademik (`/admin/rekap-nonakademik`)
- Lembar rekap terpadu menampilkan 6 aspek nonakademik untuk seluruh siswa per kelas.
- Halaman detail portofolio nonakademik siswa (`/admin/rekap-nonakademik/{student}`) yang siap dicetak (`@media print`).

---

## 5. Keamanan & Integritas Data
- RBAC ketat: Seluruh modul nonakademik hanya dapat dikelola oleh administrator / guru (`role:admin`).
- Wali siswa tidak dapat mengakses rute penilaian nonakademik (403 Forbidden).
- Validasi relasi siswa dan kelas mencegah manipulasi data siswa luar kelas.
- Pemisahan data per semester menjamin keutuhan data historis rapor antarsemester.
