<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttitudeAspect;
use App\Models\Extracurricular;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for clean production initial setup.
     */
    public function run(): void
    {
        // 1. Akun Super Administrator Bersih
        User::query()->firstOrCreate(
            ['email' => 'admin@raportku.id'],
            [
                'name' => 'Administrator Madrasah',
                'role' => 'admin',
                'password' => Hash::make('Password123!'),
            ]
        );

        // 2. Master Profil Madrasah
        SchoolProfile::query()->firstOrCreate(
            ['npsn' => '60000001'],
            [
                'name' => 'Madrasah Ibtidaiyah Negeri 1 Indonesia',
                'nsm' => '111100000001',
                'address' => 'Jl. Pendidikan Islam No. 1',
                'village' => 'Kelurahan Madrasah',
                'district' => 'Kecamatan Cendekia',
                'regency' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'principal_name' => 'Dr. H. Muhammad Ilyas, M.Pd.I.',
                'principal_nip' => '198001012005011001',
            ]
        );

        // 3. Tahun Ajaran & Semester Awal
        $year = AcademicYear::query()->firstOrCreate(
            ['name' => '2025/2026'],
            ['is_active' => true]
        );

        $semGanjil = Semester::query()->firstOrCreate(
            ['academic_year_id' => $year->id, 'name' => 'Ganjil'],
            ['is_active' => true, 'is_locked' => false]
        );

        $semGenap = Semester::query()->firstOrCreate(
            ['academic_year_id' => $year->id, 'name' => 'Genap'],
            ['is_active' => false, 'is_locked' => false]
        );

        $semGanjil->activate();

        // 4. Master Rombel Kelas 1A s.d. 6A
        foreach (range(1, 6) as $grade) {
            SchoolClass::query()->firstOrCreate(
                ['academic_year_id' => $year->id, 'name' => "{$grade}A"],
                ['grade_level' => $grade]
            );
        }

        // 5. 12 Mata Pelajaran Standar Kemenag RI
        $standardSubjects = [
            ['code' => 'PAI-QH', 'name' => "Al-Qur'an Hadis", 'order' => 1],
            ['code' => 'PAI-AA', 'name' => 'Akidah Akhlak', 'order' => 2],
            ['code' => 'PAI-FK', 'name' => 'Fikih', 'order' => 3],
            ['code' => 'PAI-SK', 'name' => 'Sejarah Kebudayaan Islam', 'order' => 4],
            ['code' => 'ARB-01', 'name' => 'Bahasa Arab', 'order' => 5],
            ['code' => 'PPN-01', 'name' => 'Pendidikan Pancasila', 'order' => 6],
            ['code' => 'BIN-01', 'name' => 'Bahasa Indonesia', 'order' => 7],
            ['code' => 'MTK-01', 'name' => 'Matematika', 'order' => 8],
            ['code' => 'IPA-01', 'name' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'order' => 9],
            ['code' => 'SBD-01', 'name' => 'Seni Budaya dan Prakarya', 'order' => 10],
            ['code' => 'PJK-01', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'order' => 11],
            ['code' => 'BIG-01', 'name' => 'Bahasa Inggris', 'order' => 12],
        ];

        foreach ($standardSubjects as $s) {
            Subject::query()->firstOrCreate(
                ['code' => $s['code']],
                ['name' => $s['name'], 'order' => $s['order'], 'is_active' => true]
            );
        }

        // 6. Aspek Karakter & Dimensi Profil Pelajar Pancasila Rahmatan lil Alamin
        $attitudeAspects = [
            ['name' => 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia', 'description' => 'Menerapkan ajaran agama dan akhlakul karimah dalam pergaulan sehari-hari.'],
            ['name' => 'Kewarganegaraan & Nilai Kebinekaan Global', 'description' => 'Menghormati keragaman budaya dan menjunjung persatuan.'],
            ['name' => 'Gotong Royong & Kepedulian Sosial', 'description' => 'Bekerja sama, tolong-menolong, dan peduli terhadap lingkungan.'],
            ['name' => 'Kemandirian Belajar', 'description' => 'Mampu bertanggung jawab atas proses dan hasil belajarnya sendiri.'],
            ['name' => 'Bernalar Kritis', 'description' => 'Memproses informasi, menganalisis penalaran, dan mengevaluasi solusi.'],
            ['name' => 'Kreativitas & Daya Inovasi', 'description' => 'Menghasilkan gagasan orisinal dan karya yang bermakna.'],
        ];

        foreach ($attitudeAspects as $a) {
            AttitudeAspect::query()->firstOrCreate(
                ['name' => $a['name']],
                ['description' => $a['description'], 'is_active' => true]
            );
        }

        // 7. Ekstrakurikuler Dasar Madrasah
        $extracurriculars = [
            ['name' => 'Pramuka Penggalang/Siaga', 'description' => 'Pendidikan kepanduan dan kedisiplinan berkarakter.'],
            ['name' => 'Tahfiz & Tilawatil Qur\'an', 'description' => 'Pembinaan hafalan juz 30 dan nagham tilawah Al-Qur\'an.'],
            ['name' => 'Olahraga & Futsal', 'description' => 'Pengembangan kebugaran jasmani dan sportivitas.'],
            ['name' => 'Seni Hadrah & Marawis', 'description' => 'Apresiasi seni budaya Islam dan rebana.'],
        ];

        foreach ($extracurriculars as $e) {
            Extracurricular::query()->firstOrCreate(
                ['name' => $e['name']],
                ['description' => $e['description'], 'is_active' => true]
            );
        }
    }
}
