<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AttitudeAspect;
use App\Models\Enrollment;
use App\Models\Extracurricular;
use App\Models\GuardianStudent;
use App\Models\HomeroomNote;
use App\Models\HomeroomTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentActivity;
use App\Models\StudentAssessmentScore;
use App\Models\StudentAttendance;
use App\Models\StudentAttitudeRating;
use App\Models\StudentAttitudeSummary;
use App\Models\StudentExtracurricular;
use App\Models\StudentSubjectResult;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tahun Ajaran
        $yearPast = AcademicYear::query()->firstOrCreate(
            ['name' => '2024/2025'],
            ['is_active' => false]
        );

        $yearCurrent = AcademicYear::query()->firstOrCreate(
            ['name' => '2025/2026'],
            ['is_active' => true]
        );

        // 2. Semester
        $semPastGanjil = Semester::query()->firstOrCreate(
            ['academic_year_id' => $yearPast->id, 'name' => 'Ganjil'],
            ['is_active' => false]
        );

        $semPastGenap = Semester::query()->firstOrCreate(
            ['academic_year_id' => $yearPast->id, 'name' => 'Genap'],
            ['is_active' => false]
        );

        $semCurrentGanjil = Semester::query()->firstOrCreate(
            ['academic_year_id' => $yearCurrent->id, 'name' => 'Ganjil'],
            ['is_active' => true]
        );

        $semCurrentGenap = Semester::query()->firstOrCreate(
            ['academic_year_id' => $yearCurrent->id, 'name' => 'Genap'],
            ['is_active' => false]
        );

        // Pastikan semester aktif diaktifkan melalui helper
        $semCurrentGanjil->activate();

        // 3. Kelas Rombel
        $classesPast = [];
        foreach (range(1, 6) as $grade) {
            $classesPast[$grade] = SchoolClass::query()->firstOrCreate(
                ['academic_year_id' => $yearPast->id, 'name' => "{$grade}A"],
                ['grade_level' => $grade]
            );
        }

        $classesCurrent = [];
        foreach (range(1, 6) as $grade) {
            $classesCurrent[$grade] = SchoolClass::query()->firstOrCreate(
                ['academic_year_id' => $yearCurrent->id, 'name' => "{$grade}A"],
                ['grade_level' => $grade]
            );
        }

        // Tambah kelas 4B khusus di tahun aktif
        $class4B = SchoolClass::query()->firstOrCreate(
            ['academic_year_id' => $yearCurrent->id, 'name' => '4B'],
            ['grade_level' => 4]
        );

        // 4. Mata Pelajaran Standar MI (Kemenag)
        $subjectsData = [
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

        $subjects = [];
        foreach ($subjectsData as $s) {
            $subjects[] = Subject::query()->firstOrCreate(
                ['code' => $s['code']],
                ['name' => $s['name'], 'order' => $s['order'], 'is_active' => true]
            );
        }

        // 5. Penugasan Mapel ke Kelas 4A pada semester aktif
        foreach ($subjects as $subject) {
            SubjectAssignment::query()->firstOrCreate([
                'semester_id' => $semCurrentGanjil->id,
                'school_class_id' => $classesCurrent[4]->id,
                'subject_id' => $subject->id,
            ]);
        }

        // 6. Data Siswa Contoh (Uji Leading Zero NIS & NISN)
        $studentsData = [
            [
                'nis' => '0101',
                'nisn' => '0081234501',
                'name' => 'Ahmad Faiz Al-Faruq',
                'gender' => 'Laki-laki',
                'birth_place' => 'Jakarta',
                'birth_date' => '2015-04-12',
                'address' => 'Jl. Madrasah No. 12, Kebayoran Baru, Jakarta Selatan',
                'status' => 'aktif',
            ],
            [
                'nis' => '0102',
                'nisn' => '0081234502',
                'name' => 'Aisyah Humaira Putri',
                'gender' => 'Perempuan',
                'birth_place' => 'Bandung',
                'birth_date' => '2017-08-25',
                'address' => 'Jl. Madrasah No. 12, Kebayoran Baru, Jakarta Selatan',
                'status' => 'aktif',
            ],
            [
                'nis' => '0103',
                'nisn' => '0081234503',
                'name' => 'Muhammad Rizky Ramadhan',
                'gender' => 'Laki-laki',
                'birth_place' => 'Surabaya',
                'birth_date' => '2015-06-18',
                'address' => 'Jl. Cempaka Putih No. 44, Jakarta Pusat',
                'status' => 'aktif',
            ],
            [
                'nis' => '0104',
                'nisn' => null, // Uji NISN opsional
                'name' => 'Fatimah Az-Zahra',
                'gender' => 'Perempuan',
                'birth_place' => 'Semarang',
                'birth_date' => '2016-01-30',
                'address' => 'Jl. Melati No. 8, Tebet, Jakarta Selatan',
                'status' => 'aktif',
            ],
            [
                'nis' => '0105',
                'nisn' => '0081234505',
                'name' => 'Bilal Habibi',
                'gender' => 'Laki-laki',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '2015-11-09',
                'address' => 'Jl. Mawar No. 19, Pasar Minggu, Jakarta Selatan',
                'status' => 'aktif',
            ],
        ];

        $students = [];
        foreach ($studentsData as $st) {
            $students[] = Student::query()->firstOrCreate(
                ['nis' => $st['nis']],
                $st
            );
        }

        // 7. Penempatan Kelas & Riwayat Antarsemester
        // Siswa 0 (Faiz): Di 2024/2025 Genap di Kelas 3A, dan 2025/2026 Ganjil di Kelas 4A (riwayat tidak tertimpa!)
        Enrollment::query()->firstOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semPastGenap->id,
        ], [
            'school_class_id' => $classesPast[3]->id,
            'notes' => 'Naik ke kelas 4A',
        ]);

        Enrollment::query()->firstOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[4]->id,
            'notes' => 'Rombel reguler',
        ]);

        // Siswa 1 (Aisyah - adik Faiz): Di 2025/2026 Ganjil di Kelas 2A
        Enrollment::query()->firstOrCreate([
            'student_id' => $students[1]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[2]->id,
            'notes' => 'Tingkat 2',
        ]);

        // Siswa 2 (Rizky): Di 2025/2026 Ganjil di Kelas 4B
        Enrollment::query()->firstOrCreate([
            'student_id' => $students[2]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $class4B->id,
            'notes' => 'Rombel 4B',
        ]);

        // 8. Akun Wali dan Hubungan Anak
        $waliDemo = User::query()->where('email', 'wali@raportku.test')->first();
        if ($waliDemo) {
            // Tautkan wali demo dengan 2 anak (Faiz dan Aisyah)
            GuardianStudent::query()->firstOrCreate([
                'user_id' => $waliDemo->id,
                'student_id' => $students[0]->id,
            ], [
                'relationship' => 'Ayah',
            ]);

            GuardianStudent::query()->firstOrCreate([
                'user_id' => $waliDemo->id,
                'student_id' => $students[1]->id,
            ], [
                'relationship' => 'Ayah',
            ]);
        }

        // Akun wali kedua tanpa anak (untuk menguji kondisi kosong di portal wali)
        User::query()->firstOrCreate(
            ['email' => 'wali.baru@raportku.test'],
            [
                'name' => 'Wali Belum Terhubung',
                'role' => 'wali',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Akun wali ketiga khusus anak Rizky (untuk menguji penolakan akses lintas wali)
        $waliRizky = User::query()->firstOrCreate(
            ['email' => 'wali.rizky@raportku.test'],
            [
                'name' => 'Ibu Rahmawati (Wali Rizky)',
                'role' => 'wali',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        GuardianStudent::query()->firstOrCreate([
            'user_id' => $waliRizky->id,
            'student_id' => $students[2]->id,
        ], [
            'relationship' => 'Ibu',
        ]);

        // 9. Komponen Penilaian & Contoh Nilai Akademik Tahap 4
        // Daftarkan siswa ke-4 (Fatimah) dan ke-5 (Bilal) ke Kelas 4A semester aktif untuk melengkapi rombel
        Enrollment::query()->firstOrCreate([
            'student_id' => $students[3]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[4]->id,
            'notes' => 'Rombel reguler 4A',
        ]);

        Enrollment::query()->firstOrCreate([
            'student_id' => $students[4]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[4]->id,
            'notes' => 'Rombel reguler 4A',
        ]);

        // Komponen standar untuk seluruh mapel pada semester aktif
        $standardComponents = [
            ['name' => 'Tugas & Praktik', 'weight' => 40.00, 'order' => 1],
            ['name' => 'Penilaian Tengah Semester (PTS)', 'weight' => 30.00, 'order' => 2],
            ['name' => 'Penilaian Akhir Semester (PAS)', 'weight' => 30.00, 'order' => 3],
        ];

        foreach ($subjects as $subject) {
            foreach ($standardComponents as $sc) {
                AssessmentComponent::query()->firstOrCreate([
                    'subject_id' => $subject->id,
                    'semester_id' => $semCurrentGanjil->id,
                    'name' => $sc['name'],
                ], [
                    'weight' => $sc['weight'],
                    'order' => $sc['order'],
                    'is_active' => true,
                ]);
            }
        }

        // Ambil penugasan mapel Al-Qur'an Hadis di Kelas 4A
        $qhAssignment = SubjectAssignment::query()
            ->where('semester_id', $semCurrentGanjil->id)
            ->where('school_class_id', $classesCurrent[4]->id)
            ->where('subject_id', $subjects[0]->id)
            ->first();

        if ($qhAssignment) {
            $qhComponents = AssessmentComponent::query()
                ->where('subject_id', $subjects[0]->id)
                ->where('semester_id', $semCurrentGanjil->id)
                ->orderBy('order')
                ->get();

            // Nilai Lengkap untuk Faiz (Siswa 0):
            // Tugas (40%): 88.00, PTS (30%): 85.00, PAS (30%): 90.00
            // Nilai Akhir: 88*0.4 + 85*0.3 + 90*0.3 = 35.2 + 25.5 + 27 = 87.70
            if ($qhComponents->count() === 3) {
                $compTugas = $qhComponents[0];
                $compPts = $qhComponents[1];
                $compPas = $qhComponents[2];

                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[0]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compTugas->id,
                ], ['score' => 88.00]);

                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[0]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compPts->id,
                ], ['score' => 85.00]);

                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[0]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compPas->id,
                ], ['score' => 90.00]);

                StudentSubjectResult::query()->updateOrCreate([
                    'student_id' => $students[0]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                ], [
                    'final_score' => 87.70,
                    'competency_description' => "Menunjukkan penguasaan sangat baik dalam membaca dan memahami kandungan surat-surat pendek Al-Qur'an Hadis.",
                    'is_complete' => true,
                ]);

                // Nilai Lengkap untuk Bilal (Siswa 4):
                // Tugas (40%): 75.00, PTS (30%): 78.00, PAS (30%): 74.00
                // Nilai Akhir: 75*0.4 + 78*0.3 + 74*0.3 = 30 + 23.4 + 22.2 = 75.60
                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[4]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compTugas->id,
                ], ['score' => 75.00]);

                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[4]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compPts->id,
                ], ['score' => 78.00]);

                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[4]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compPas->id,
                ], ['score' => 74.00]);

                StudentSubjectResult::query()->updateOrCreate([
                    'student_id' => $students[4]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                ], [
                    'final_score' => 75.60,
                    'competency_description' => "Menunjukkan pemahaman yang baik dalam materi pokok Al-Qur'an Hadis.",
                    'is_complete' => true,
                ]);

                // Nilai Belum Lengkap untuk Fatimah (Siswa 3):
                // Hanya tugas 80.00, PTS dan PAS belum ada (null)
                StudentAssessmentScore::query()->updateOrCreate([
                    'student_id' => $students[3]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                    'assessment_component_id' => $compTugas->id,
                ], ['score' => 80.00]);

                StudentSubjectResult::query()->updateOrCreate([
                    'student_id' => $students[3]->id,
                    'subject_assignment_id' => $qhAssignment->id,
                ], [
                    'final_score' => null,
                    'competency_description' => null,
                    'is_complete' => false,
                ]);
            }
        }

        // 10. Data Penilaian Nonakademik Tahap 5
        $adminUser = User::query()->where('role', 'admin')->first();

        // 10.1 Aspek Sikap Standar MI
        $standards = [
            ['name' => 'Kedisiplinan', 'description' => 'Ketaatan terhadap tata tertib, ketepatan waktu hadir dan mengumpulkan tugas.', 'order' => 1],
            ['name' => 'Tanggung Jawab', 'description' => 'Kesungguhan dalam menjalankan amanah dan menyelesaikan kewajiban belajar.', 'order' => 2],
            ['name' => 'Sopan Santun', 'description' => 'Perilaku hormat kepada guru, orang tua, dan santun bertutur kata sesama teman.', 'order' => 3],
            ['name' => 'Kejujuran', 'description' => 'Kesesuaian antara ucapan dan perbuatan, tidak berbohong atau berbuat curang.', 'order' => 4],
            ['name' => 'Kerja Sama', 'description' => 'Kemampuan bergotong royong dan aktif berkontribusi dalam kegiatan kelompok.', 'order' => 5],
            ['name' => 'Kemandirian', 'description' => 'Prakarsa dan inisiatif pribadi dalam belajar tanpa bergantung berlebih.', 'order' => 6],
            ['name' => 'Kepedulian', 'description' => 'Kepekaan sosial, empati, dan kepedulian terhadap kebersihan lingkungan madrasah.', 'order' => 7],
        ];

        $attitudeAspectModels = [];
        foreach ($standards as $s) {
            $attitudeAspectModels[] = AttitudeAspect::query()->firstOrCreate(
                ['name' => $s['name']],
                ['description' => $s['description'], 'order' => $s['order'], 'is_active' => true]
            );
        }

        // 10.2 Penilaian Sikap untuk Faiz & Bilal
        if (! empty($attitudeAspectModels)) {
            foreach ($attitudeAspectModels as $asp) {
                StudentAttitudeRating::query()->updateOrCreate([
                    'student_id' => $students[0]->id,
                    'semester_id' => $semCurrentGanjil->id,
                    'attitude_aspect_id' => $asp->id,
                ], [
                    'rating' => 'Sangat Baik',
                    'development_notes' => 'Menunjukkan teladan yang sangat baik dalam aspek '.strtolower($asp->name).'.',
                    'assessed_by_user_id' => $adminUser?->id,
                ]);

                StudentAttitudeRating::query()->updateOrCreate([
                    'student_id' => $students[4]->id,
                    'semester_id' => $semCurrentGanjil->id,
                    'attitude_aspect_id' => $asp->id,
                ], [
                    'rating' => 'Baik',
                    'development_notes' => 'Konsisten menunjukkan perilaku '.strtolower($asp->name).' yang baik di lingkungan madrasah.',
                    'assessed_by_user_id' => $adminUser?->id,
                ]);
            }

            StudentAttitudeSummary::query()->updateOrCreate([
                'student_id' => $students[0]->id,
                'semester_id' => $semCurrentGanjil->id,
            ], [
                'general_notes' => 'Ananda Ahmad Faiz senantiasa berakhlak terpuji, menghormati ustadz/ustadzah, dan aktif membantu teman.',
                'assessed_by_user_id' => $adminUser?->id,
            ]);
        }

        // 10.3 Presensi Kehadiran Siswa Kelas 4A (Contoh 5 hari efektif)
        $sampleDates = [
            '2026-10-05',
            '2026-10-06',
            '2026-10-07',
            '2026-10-08',
            '2026-10-09',
        ];

        foreach ($sampleDates as $dayIdx => $dateVal) {
            // Faiz: Hadir setiap hari
            StudentAttendance::query()->updateOrCreate([
                'student_id' => $students[0]->id,
                'date' => $dateVal,
                'semester_id' => $semCurrentGanjil->id,
            ], [
                'school_class_id' => $classesCurrent[4]->id,
                'status' => 'Hadir',
                'notes' => null,
                'recorded_by_user_id' => $adminUser?->id,
            ]);

            // Bilal: Hari ke-4 Sakit, hari lainnya Hadir
            StudentAttendance::query()->updateOrCreate([
                'student_id' => $students[4]->id,
                'date' => $dateVal,
                'semester_id' => $semCurrentGanjil->id,
            ], [
                'school_class_id' => $classesCurrent[4]->id,
                'status' => ($dayIdx === 3) ? 'Sakit' : 'Hadir',
                'notes' => ($dayIdx === 3) ? 'Surat dokter terlampir' : null,
                'recorded_by_user_id' => $adminUser?->id,
            ]);

            // Fatimah: Hari ke-2 Izin, hari lainnya Hadir
            StudentAttendance::query()->updateOrCreate([
                'student_id' => $students[3]->id,
                'date' => $dateVal,
                'semester_id' => $semCurrentGanjil->id,
            ], [
                'school_class_id' => $classesCurrent[4]->id,
                'status' => ($dayIdx === 1) ? 'Izin' : 'Hadir',
                'notes' => ($dayIdx === 1) ? 'Acara keluarga' : null,
                'recorded_by_user_id' => $adminUser?->id,
            ]);
        }

        // 10.4 Keaktifan Belajar Siswa
        StudentActivity::query()->firstOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
            'aspect' => 'Keaktifan Bertanya',
        ], [
            'date' => '2026-10-07',
            'description' => 'Sangat aktif mengajukan pertanyaan mendalam saat pemaparan materi adab menuntut ilmu.',
            'teacher_notes' => 'Pertanyaan kritis dan relevan dengan materi.',
            'recorded_by_user_id' => $adminUser?->id,
        ]);

        StudentActivity::query()->firstOrCreate([
            'student_id' => $students[4]->id,
            'semester_id' => $semCurrentGanjil->id,
            'aspect' => 'Kerja Sama Kelompok',
        ], [
            'date' => '2026-10-08',
            'description' => 'Memimpin diskusi kelompok proyek IPAS dengan penuh kekompakan dan inisiatif tinggi.',
            'teacher_notes' => 'Mampu mengkoordinasikan tugas sesama teman.',
            'recorded_by_user_id' => $adminUser?->id,
        ]);

        // 10.5 Master Ekstrakurikuler & Partisipasi
        $extrasData = [
            ['name' => 'Pramuka Penggalang', 'category' => 'Kepanduan', 'description' => 'Kegiatan kepanduan wajib untuk melatih kedisiplinan dan kemandirian.'],
            ['name' => 'Seni Baca Al-Qur\'an (Tilawah)', 'category' => 'Keagamaan', 'description' => 'Pendalaman seni membaca Al-Qur\'an dengan lagu dan kaidah tajwid.'],
            ['name' => 'Futsal', 'category' => 'Olahraga', 'description' => 'Latihan ketangkasan fisik, stamina, dan kerja sama tim dalam olahraga futsal.'],
            ['name' => 'Kaligrafi Islam', 'category' => 'Seni & Budaya', 'description' => 'Pembinaan seni tulis huruf Arab indah khat naskhi dan riq\'ah.'],
            ['name' => 'Dokter Kecil / UKS', 'category' => 'Kesehatan', 'description' => 'Pelatihan pertolongan pertama dan pembiasaan hidup sehat di madrasah.'],
        ];

        $extraModels = [];
        foreach ($extrasData as $ex) {
            $extraModels[] = Extracurricular::query()->firstOrCreate(
                ['name' => $ex['name']],
                ['category' => $ex['category'], 'description' => $ex['description'], 'is_active' => true]
            );
        }

        // Partisipasi Siswa dalam Ekskul
        StudentExtracurricular::query()->updateOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
            'extracurricular_id' => $extraModels[0]->id, // Pramuka
        ], [
            'predicate' => 'Sangat Baik',
            'description' => 'Sangat aktif mengikuti latihan mingguan dan menguasai teknik semaphore serta pionering dasar.',
        ]);

        StudentExtracurricular::query()->updateOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
            'extracurricular_id' => $extraModels[1]->id, // Tilawah
        ], [
            'predicate' => 'Sangat Baik',
            'description' => 'Menunjukkan kualitas makhorijul huruf yang fasih dan penghayatan irama nagham bayati yang sangat merdu.',
        ]);

        StudentExtracurricular::query()->updateOrCreate([
            'student_id' => $students[4]->id,
            'semester_id' => $semCurrentGanjil->id,
            'extracurricular_id' => $extraModels[2]->id, // Futsal
        ], [
            'predicate' => 'Baik',
            'description' => 'Memiliki ketahanan fisik yang prima dan menjunjung tinggi sportivitas dalam permainan tim.',
        ]);

        // 10.6 Prestasi Siswa
        StudentAchievement::query()->firstOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
            'achievement_name' => 'Lomba Tahfiz Al-Qur\'an Juz 30 Gebyar Muharram',
        ], [
            'level' => 'Kecamatan',
            'activity_type' => 'Keagamaan',
            'rank' => 'Juara 1',
            'organizer' => 'Kantor Urusan Agama & Forum Guru MI Kecamatan',
            'date' => '2026-08-20',
            'notes' => 'Mendapatkan piala bergilir dan sertifikat penghargaan.',
        ]);

        StudentAchievement::query()->firstOrCreate([
            'student_id' => $students[4]->id,
            'semester_id' => $semCurrentGanjil->id,
            'achievement_name' => 'Turnamen Futsal Pelajar MI',
        ], [
            'level' => 'Kabupaten/Kota',
            'activity_type' => 'Olahraga',
            'rank' => 'Juara 2',
            'organizer' => 'Kelompok Kerja Madrasah Ibtidaiyah (KKMI)',
            'date' => '2026-09-15',
            'notes' => 'Menjadi penjaga gawang terbaik selama turnamen berlangsung.',
        ]);

        // 10.7 Catatan Wali Kelas
        HomeroomNote::query()->updateOrCreate([
            'student_id' => $students[0]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[4]->id,
            'academic_progress' => 'Ahmad Faiz menunjukkan pencapaian akademik yang sangat unggul di semua mata pelajaran, terutama bidang Al-Qur\'an Hadis dan Matematika.',
            'attitude_behavior' => 'Berakhlakul karimah, santun dalam berbicara kepada guru, dan menjadi teladan bagi rekan-rekan sekelasnya.',
            'discipline' => 'Sangat tertib mematuhi tata tertib madrasah, presensi sempurna tanpa alpa.',
            'student_potential' => 'Memiliki bakat luar biasa dalam bidang hafalan Al-Qur\'an dan penalaran logika sains.',
            'areas_for_improvement' => 'Pertahankan kerendahan hati dan terus asah keterampilan kepemimpinan dalam kegiatan organisasi madrasah.',
            'parent_advice' => 'Terima kasih atas kerja sama luar biasa Ayah dan Ibu dalam mengawal muroja\'ah dan ibadah harian Faiz di rumah.',
            'author_user_id' => $adminUser?->id,
        ]);

        HomeroomNote::query()->updateOrCreate([
            'student_id' => $students[4]->id,
            'semester_id' => $semCurrentGanjil->id,
        ], [
            'school_class_id' => $classesCurrent[4]->id,
            'academic_progress' => 'Bilal menunjukkan semangat belajar yang sangat baik dan tekun dalam menyelesaikan tugas-tugas kelompok.',
            'attitude_behavior' => 'Ramah, supel, dan mudah bergaul dengan seluruh teman sekelas.',
            'discipline' => 'Disiplin dan bertanggung jawab terhadap kebersihan kelas.',
            'student_potential' => 'Bakat fisik dan kecerdasan kinestetik dalam bidang olahraga futsal sangat menonjol.',
            'areas_for_improvement' => 'Perlu meningkatkan ketelitian dalam membaca soal-soal narasi panjang pada mata pelajaran Bahasa Indonesia.',
            'parent_advice' => 'Mohon pendampingan membaca buku cerita edukatif bersama Ayah dan Ibu selama 15 menit setiap malam di rumah.',
            'author_user_id' => $adminUser?->id,
        ]);

        // 11. Profil Madrasah & Penugasan Wali Kelas
        SchoolProfile::query()->firstOrCreate(
            ['npsn' => '60712345'],
            [
                'name' => 'Madrasah Ibtidaiyah Al-Hidayah',
                'nsm' => '111231710001',
                'address' => 'Jl. Madrasah Raya No. 45, Kebayoran Baru',
                'village' => 'Kebayoran Baru',
                'district' => 'Kebayoran Baru',
                'regency' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'principal_name' => 'Dr. H. Muhammad Ilyas, M.Pd.I.',
                'principal_nip' => '197805122003121002',
            ]
        );

        HomeroomTeacherAssignment::query()->firstOrCreate(
            [
                'school_class_id' => $classesCurrent[4]->id,
                'semester_id' => $semCurrentGanjil->id,
            ],
            [
                'teacher_name' => 'Ustadzah Siti Rahmawati, S.Pd.I.',
                'teacher_nip' => '198509142010012025',
            ]
        );
    }
}
