# Tahap 6 Rapor Draf — Desain

## Intent

Admin menyusun, memeriksa, dan mencetak pratinjau browser buku rapor digital per siswa dan semester. Rapor ini merupakan draf internal: bukan penerbitan, penguncian nilai, PDF resmi, atau akses wali.

## Goals

- Menyimpan snapshot rapor per siswa dan semester secara atomik, termasuk identitas madrasah, logo, siswa, penempatan historis, akademik, nonakademik, dan penandatangan.
- Menampilkan daftar pengelolaan rapor, checklist kelengkapan, detail, pratinjau A4, serta cetak browser.
- Menandai perubahan sumber, termasuk tambah/hapus data relevan, tanpa pernah memperbarui snapshot otomatis.
- Mempertahankan perbedaan nilai `null` dan `0`, serta menolak akses wali dan relasi siswa/kelas/semester yang tidak sesuai.

## Non-goals

- Penerbitan, penguncian nilai, tanda tangan digital, PDF resmi, arsip versi final, dan distribusi/unduhan wali.
- Perubahan data nilai atau nonakademik sumber ketika snapshot dibuat maupun diperbarui.

## Constraints

- Laravel 13.37, PHP 8.4, SQLite, Blade, Tailwind CSS v4; tanpa dependensi baru.
- Snapshot selalu memakai `enrollments` pada semester yang dipilih, tidak pernah kelas aktif siswa saat ini.
- Satu rapor draf unik untuk setiap `student_id` dan `semester_id`.
- Semua rute berada di grup `auth` dan `role:admin` yang telah ada.
- Bagian opsional (prestasi, ekstrakurikuler, keaktifan) tidak menggagalkan kelengkapan.

## Approaches considered

1. Membaca seluruh data langsung dari tabel sumber. Ini paling sederhana, tetapi histori pratinjau dan logo lama berubah sehingga tidak memenuhi kebutuhan.
2. Menyimpan salinan parsial lalu tetap membaca detail dari sumber. Ini mengurangi duplikasi tetapi tetap memungkinkan perubahan data sumber bocor ke draf.
3. **Dipilih: satu snapshot JSON lengkap per rapor, dengan fingerprint sumber.** Isi draf stabil, pembaruan eksplisit dan teratomik, deteksi perubahan murah melalui hash normalisasi. Pilihan ini konsisten dengan permintaan snapshot per rapor.

## Data design

Tambah `school_profiles` sebagai satu profil pengaturan madrasah yang dapat dikelola admin. Kolom menyimpan identitas, lokasi, logo path, nama/NIP kepala madrasah. Nilai yang kosong tetap `null` dan UI menandai informasi yang belum lengkap. Tambah `homeroom_teacher_assignments` untuk penugasan wali kelas per `school_class_id` dan `semester_id`; nama dan NIP wali kelas disalin ke snapshot.

Tambah `report_drafts` dengan foreign key ke student, semester, enrollment, dan pengguna pembaru terakhir; unique `student_id, semester_id`; `snapshot` JSON; `source_fingerprint` string; `created_at`/`updated_at`. Snapshot berisi semua teks, nilai, angka kehadiran, daftar akademik dan nonakademik, path/logo data URI yang dibutuhkan oleh tampilan lama, metadata kelas/tahun/semester, dan waktu/admin pembaruan. Foreign key menjaga hubungan saat ini; render draf membaca `snapshot`, bukan model sumber.

`ReportDraftBuilder` membangun payload sumber dalam transaksi database: memverifikasi enrollment historis, mengambil subject assignment persis kelas+semester, hasil akhir yang sudah dihitung server, serta data nonakademik yang dibatasi siswa+semester. Payload kanonik juga di-hash untuk fingerprint. Operasi create-or-update memakai `lockForUpdate()` dan unique index agar tidak ada draf ganda/race condition. Tidak ada perintah tulis pada tabel sumber.

## Completeness and drift

`ReportCompletenessChecker` mengevaluasi payload sumber saat ini. Wajib: nama, NIS, jenis kelamin, tempat/tanggal lahir siswa; nama, NPSN, alamat, desa/kelurahan, kecamatan, kabupaten, provinsi, dan nama kepala madrasah; enrollment valid; setiap penugasan mapel memiliki hasil final `is_complete` dan deskripsi; rating sikap aktif; ada data rekap kehadiran; dan catatan wali kelas. Logo, NSM, serta NIP tidak wajib. Hasil mengembalikan daftar kekurangan bertaut ke modul sumber dan status: `Belum Disusun`, `Belum Lengkap`, `Siap Ditinjau`, atau `Memerlukan Perbaikan` bila draf ada namun fingerprint sudah berbeda. Draf boleh dibuat ketika belum lengkap.

Ketika halaman draf dibuka, aplikasi membangun fingerprint sumber saat ini secara read-only dan membandingkannya dengan draf. Hash mencakup daftar berurutan serta isi semua catatan agar perubahan, penambahan, dan penghapusan terdeteksi. Ia tidak mengganti `snapshot` hingga admin POST ke endpoint pembaruan yang spesifik bagi draf tersebut.

## UI and routes

- `GET /admin/pengelolaan-rapor`: filter tahun ajaran, semester, kelas; daftar enrollment dan status.
- `GET /admin/pengelolaan-rapor/{student}`: detail/checklist untuk semester terpilih; memastikan student terdaftar di kelas semester tersebut.
- `POST /admin/pengelolaan-rapor/{student}/pratinjau`: membuat atau mengganti snapshot draf hanya untuk siswa+semester yang dipilih; flash menjelaskan isi draf diganti.
- `GET /admin/pengelolaan-rapor/{student}/pratinjau`: merender snapshot sebagai dokumen A4 multi-halaman, menyediakan Kembali, Perbarui Pratinjau, dan Cetak Pratinjau.

Halaman responsif memakai komponen/dashboard existing; CSS `@media print` mengatur ukuran A4, page breaks, nomor halaman, dan menyembunyikan navigasi/tombol.

## Verification

Feature tests mencakup snapshot stabil setelah profil/logo sumber berubah, pemisahan semester, pembaruan satu draf tanpa memengaruhi draf lain, tabel sumber tak berubah, data kosong/null versus nol, kelengkapan, perubahan data sumber, akses wali/ID manipulatif ditolak, serta render pratinjau. Jalankan suite penuh, Pint, dan Vite build.
