<?php

use App\Models\AcademicYear;
use App\Models\AttitudeAspect;
use App\Models\Enrollment;
use App\Models\Extracurricular;
use App\Models\HomeroomNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentActivity;
use App\Models\StudentAttendance;
use App\Models\StudentAttitudeRating;
use App\Models\StudentAttitudeSummary;
use App\Models\StudentExtracurricular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->wali = User::factory()->wali()->create();

    $this->year = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
    $this->semester = Semester::factory()->create([
        'academic_year_id' => $this->year->id,
        'name' => 'Ganjil',
        'is_active' => true,
    ]);
    $this->class = SchoolClass::factory()->create([
        'academic_year_id' => $this->year->id,
        'name' => '4A',
        'grade_level' => 4,
    ]);

    $this->student1 = Student::factory()->create(['name' => 'Ahmad Faiz', 'nis' => '0101']);
    $this->student2 = Student::factory()->create(['name' => 'Bilal Habibi', 'nis' => '0102']);

    Enrollment::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $this->student2->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
    ]);
});

// ==================== 1. PENILAIAN SIKAP ====================

it('allows admin to manage attitude aspects and rate student attitudes', function (): void {
    $aspect = AttitudeAspect::factory()->create([
        'name' => 'Kedisiplinan',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.attitudes.index', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
        ]))
        ->assertOk()
        ->assertSee('Penilaian Sikap dan Perilaku')
        ->assertSee('Ahmad Faiz')
        ->assertSee('Kedisiplinan');

    // Input penilaian sikap
    $this->actingAs($this->admin)
        ->post(route('admin.attitudes.store'), [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'ratings' => [
                $this->student1->id => [
                    $aspect->id => 'Sangat Baik',
                ],
            ],
            'notes' => [
                $this->student1->id => [
                    $aspect->id => 'Selalu tepat waktu.',
                ],
            ],
            'summaries' => [
                $this->student1->id => 'Akhlak terpuji dan teladan bagi teman.',
            ],
        ])
        ->assertRedirect();

    $rating = StudentAttitudeRating::query()
        ->where('student_id', $this->student1->id)
        ->where('semester_id', $this->semester->id)
        ->where('attitude_aspect_id', $aspect->id)
        ->first();

    expect($rating)->not->toBeNull();
    expect($rating->rating)->toEqual('Sangat Baik');
    expect($rating->development_notes)->toEqual('Selalu tepat waktu.');

    $summary = StudentAttitudeSummary::query()
        ->where('student_id', $this->student1->id)
        ->where('semester_id', $this->semester->id)
        ->first();
    expect($summary)->not->toBeNull();
    expect($summary->general_notes)->toEqual('Akhlak terpuji dan teladan bagi teman.');
});

it('rejects attitude rating for student not enrolled in the selected class', function (): void {
    $otherStudent = Student::factory()->create(['name' => 'Siswa Luar']);
    $aspect = AttitudeAspect::factory()->create(['name' => 'Kejujuran']);

    $this->actingAs($this->admin)
        ->post(route('admin.attitudes.store'), [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'ratings' => [
                $otherStudent->id => [
                    $aspect->id => 'Baik',
                ],
            ],
        ])
        ->assertForbidden();
});

// ==================== 2. KEHADIRAN SISWA ====================

it('records bulk daily attendance and prevents duplicate records for same student and date', function (): void {
    $date = '2026-10-15';

    // Simpan presensi pertama
    $this->actingAs($this->admin)
        ->post(route('admin.attendances.store-daily'), [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'date' => $date,
            'attendances' => [
                $this->student1->id => 'Hadir',
                $this->student2->id => 'Sakit',
            ],
            'notes' => [
                $this->student2->id => 'Demam flu',
            ],
        ])
        ->assertRedirect();

    expect(StudentAttendance::query()->where('date', $date)->count())->toEqual(2);

    $faizAtt = StudentAttendance::query()
        ->where('student_id', $this->student1->id)
        ->where('date', $date)
        ->first();
    expect($faizAtt->status)->toEqual('Hadir');

    // Update presensi di tanggal yang sama (misal Faiz ternyata izin, bukan hadir)
    $this->actingAs($this->admin)
        ->post(route('admin.attendances.store-daily'), [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'date' => $date,
            'attendances' => [
                $this->student1->id => 'Izin',
                $this->student2->id => 'Sakit',
            ],
            'notes' => [
                $this->student1->id => 'Keperluan keluarga',
            ],
        ])
        ->assertRedirect();

    // Jumlah baris tidak bertambah (tidak duplikat), melainkan diperbarui
    expect(StudentAttendance::query()->where('date', $date)->count())->toEqual(2);

    $faizAtt->refresh();
    expect($faizAtt->status)->toEqual('Izin');
    expect($faizAtt->notes)->toEqual('Keperluan keluarga');
});

it('calculates semester attendance recap accurately and flags when no data exists', function (): void {
    // Awalnya belum ada kehadiran sama sekali
    $response = $this->actingAs($this->admin)
        ->get(route('admin.attendances.index', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'tab' => 'recap',
        ]))
        ->assertOk()
        ->assertSee('Belum Tersedia Data Kehadiran');

    // Tambah presensi 3 hari untuk student1 (2 Hadir, 1 Sakit)
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'date' => '2026-10-01',
        'status' => 'Hadir',
    ]);
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'date' => '2026-10-02',
        'status' => 'Hadir',
    ]);
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'date' => '2026-10-03',
        'status' => 'Sakit',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.attendances.index', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'tab' => 'recap',
        ]))
        ->assertOk()
        ->assertSee('Total Hari Efektif Terdata:')
        ->assertSee('66.7%'); // 2 hadir dari 3 total hari = 66.7%
});

// ==================== 3. KEAKTIFAN SISWA ====================

it('records student learning activity observations', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.activities.store'), [
            'student_id' => $this->student1->id,
            'semester_id' => $this->semester->id,
            'date' => '2026-10-10',
            'aspect' => 'Keaktifan Bertanya',
            'description' => 'Mengajukan pertanyaan kritis saat materi rukun iman.',
            'teacher_notes' => 'Sangat antusias.',
        ])
        ->assertRedirect();

    $activity = StudentActivity::query()
        ->where('student_id', $this->student1->id)
        ->where('aspect', 'Keaktifan Bertanya')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->description)->toEqual('Mengajukan pertanyaan kritis saat materi rukun iman.');
});

// ==================== 4. EKSTRAKURIKULER ====================

it('manages extracurricular masters and records student participations', function (): void {
    $extra = Extracurricular::factory()->create([
        'name' => 'Pramuka Penggalang',
        'category' => 'Kepanduan',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.extracurriculars.store-participation'), [
            'student_id' => $this->student1->id,
            'semester_id' => $this->semester->id,
            'extracurricular_id' => $extra->id,
            'predicate' => 'Sangat Baik',
            'description' => 'Menguasai sandi morse dan simpul dasar.',
        ])
        ->assertRedirect();

    $part = StudentExtracurricular::query()
        ->where('student_id', $this->student1->id)
        ->where('extracurricular_id', $extra->id)
        ->first();

    expect($part)->not->toBeNull();
    expect($part->predicate)->toEqual('Sangat Baik');
});

// ==================== 5. PRESTASI SISWA ====================

it('records student achievements properly', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.achievements.store'), [
            'student_id' => $this->student1->id,
            'semester_id' => $this->semester->id,
            'achievement_name' => 'Lomba Kaligrafi Islam',
            'level' => 'Kabupaten/Kota',
            'activity_type' => 'Seni & Budaya',
            'rank' => 'Juara 1',
            'organizer' => 'Kemenag Kab. Sukabumi',
            'date' => '2026-09-20',
            'notes' => 'Kategori khat naskhi',
        ])
        ->assertRedirect();

    $achievement = StudentAchievement::query()
        ->where('student_id', $this->student1->id)
        ->where('achievement_name', 'Lomba Kaligrafi Islam')
        ->first();

    expect($achievement)->not->toBeNull();
    expect($achievement->level)->toEqual('Kabupaten/Kota');
    expect($achievement->rank)->toEqual('Juara 1');
});

// ==================== 6. CATATAN WALI KELAS ====================

it('saves comprehensive homeroom teacher evaluation notes', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.homeroom-notes.store'), [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'notes' => [
                $this->student1->id => [
                    'academic_progress' => 'Kemampuan membaca Al-Qur\'an sangat lancar.',
                    'attitude_behavior' => 'Santun dan ramah kepada semua guru.',
                    'discipline' => 'Selalu tepat waktu.',
                    'student_potential' => 'Bakat menghafal cepat.',
                    'areas_for_improvement' => 'Tingkatkan ketelitian hitungan.',
                    'parent_advice' => 'Teruskan pendampingan di rumah.',
                ],
            ],
        ])
        ->assertRedirect();

    $note = HomeroomNote::query()
        ->where('student_id', $this->student1->id)
        ->where('semester_id', $this->semester->id)
        ->first();

    expect($note)->not->toBeNull();
    expect($note->academic_progress)->toEqual('Kemampuan membaca Al-Qur\'an sangat lancar.');
    expect($note->parent_advice)->toEqual('Teruskan pendampingan di rumah.');
});

// ==================== 7. REKAP NONAKADEMIK & DETAIL SISWA ====================

it('displays integrated non-academic summary and student detail page', function (): void {
    // Buat data catatan lengkap untuk student1
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'status' => 'Hadir',
    ]);

    $extra = Extracurricular::factory()->create(['name' => 'Futsal']);
    StudentExtracurricular::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'extracurricular_id' => $extra->id,
        'predicate' => 'Baik',
    ]);

    StudentAchievement::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'achievement_name' => 'Medali Emas Lari 100m',
        'rank' => 'Juara 1',
    ]);

    // Akses halaman rekap kelas
    $this->actingAs($this->admin)
        ->get(route('admin.non-academic-recap.index', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
        ]))
        ->assertOk()
        ->assertSee('Ahmad Faiz')
        ->assertSee('Futsal');

    // Akses halaman detail siswa
    $this->actingAs($this->admin)
        ->get(route('admin.non-academic-recap.show', [
            'student' => $this->student1->id,
            'semester_id' => $this->semester->id,
        ]))
        ->assertOk()
        ->assertSee('Ahmad Faiz')
        ->assertSee('Medali Emas Lari 100m');
});

// ==================== 8. KEAMANAN & OTORISASI ====================

it('blocks non-admin users from accessing non-academic administration routes', function (): void {
    $this->actingAs($this->wali)
        ->get(route('admin.attitudes.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.attendances.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.activities.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.extracurriculars.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.achievements.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.homeroom-notes.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.non-academic-recap.index'))
        ->assertForbidden();
});

// ==================== 9. INTEGRITAS HISTORIS ANTARSEMESTER ====================

it('preserves non-academic records across different semesters without overwriting', function (): void {
    $pastSemester = Semester::factory()->create([
        'academic_year_id' => $this->year->id,
        'name' => 'Semester Lalu',
    ]);

    // Data di semester lalu
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $pastSemester->id,
        'school_class_id' => $this->class->id,
        'status' => 'Izin',
        'date' => '2026-05-10',
    ]);

    HomeroomNote::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $pastSemester->id,
        'school_class_id' => $this->class->id,
        'academic_progress' => 'Catatan semester lalu.',
    ]);

    // Data di semester aktif
    StudentAttendance::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'status' => 'Hadir',
        'date' => '2026-10-10',
    ]);

    HomeroomNote::factory()->create([
        'student_id' => $this->student1->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'academic_progress' => 'Catatan semester baru.',
    ]);

    // Pastikan catatan semester lampau tetap utuh
    $pastNote = HomeroomNote::query()
        ->where('student_id', $this->student1->id)
        ->where('semester_id', $pastSemester->id)
        ->first();
    expect($pastNote->academic_progress)->toEqual('Catatan semester lalu.');

    $currentNote = HomeroomNote::query()
        ->where('student_id', $this->student1->id)
        ->where('semester_id', $this->semester->id)
        ->first();
    expect($currentNote->academic_progress)->toEqual('Catatan semester baru.');

    expect(StudentAttendance::query()->where('student_id', $this->student1->id)->count())->toEqual(2);
});
