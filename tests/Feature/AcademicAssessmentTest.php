<?php

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAssessmentScore;
use App\Models\StudentSubjectResult;
use App\Models\Subject;
use App\Models\SubjectAssignment;
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

    $this->subject = Subject::factory()->create([
        'code' => 'PAI-QH',
        'name' => "Al-Qur'an Hadis",
        'order' => 1,
        'is_active' => true,
    ]);

    $this->assignment = SubjectAssignment::factory()->create([
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
        'subject_id' => $this->subject->id,
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

// ==================== 1. KOMPONEN PENILAIAN ====================

it('allows admin to manage assessment components', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.assessments.components.index', [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
        ]))
        ->assertOk()
        ->assertSee('Pengaturan Komponen Penilaian');

    // Tambah komponen Tugas 40%
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.components.store'), [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'name' => 'Tugas & Praktik',
            'weight' => 40.0,
            'order' => 1,
            'is_active' => 1,
        ])
        ->assertRedirect();

    expect(AssessmentComponent::query()->where('name', 'Tugas & Praktik')->exists())->toBeTrue();
});

it('rejects duplicate component name for same subject and semester', function (): void {
    AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'PTS',
        'weight' => 30.0,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.assessments.components.store'), [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'name' => 'PTS',
            'weight' => 30.0,
        ])
        ->assertSessionHasErrors(['name']);
});

it('prevents total active weight from exceeding 100 percent', function (): void {
    AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Tugas',
        'weight' => 60.0,
        'is_active' => true,
    ]);

    // Menambah bobot 50% akan membuat total 110% (harus ditolak)
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.components.store'), [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'name' => 'Ulangan Harian',
            'weight' => 50.0,
            'is_active' => 1,
        ])
        ->assertSessionHasErrors(['weight']);
});

it('applies standard 3 and standard 4 presets properly', function (): void {
    // Preset Standard 3: Tugas (40%), PTS (30%), PAS (30%)
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.components.preset'), [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'preset_type' => 'standard_3',
        ])
        ->assertRedirect();

    $comps3 = AssessmentComponent::query()
        ->where('subject_id', $this->subject->id)
        ->where('semester_id', $this->semester->id)
        ->where('is_active', true)
        ->get();

    expect($comps3)->toHaveCount(3);
    expect($comps3->sum('weight'))->toEqual(100.0);

    // Preset Standard 4: Tugas (20%), UH (30%), PTS (25%), PAS (25%)
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.components.preset'), [
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'preset_type' => 'standard_4',
        ])
        ->assertRedirect();

    $comps4 = AssessmentComponent::query()
        ->where('subject_id', $this->subject->id)
        ->where('semester_id', $this->semester->id)
        ->where('is_active', true)
        ->get();

    expect($comps4)->toHaveCount(4);
    expect($comps4->sum('weight'))->toEqual(100.0);
});

it('deactivates component instead of deleting if student scores exist', function (): void {
    $comp = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Tugas 1',
        'weight' => 50.0,
        'is_active' => true,
    ]);

    StudentAssessmentScore::factory()->create([
        'student_id' => $this->student1->id,
        'subject_assignment_id' => $this->assignment->id,
        'assessment_component_id' => $comp->id,
        'score' => 85.0,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.assessments.components.destroy', $comp))
        ->assertRedirect();

    // Komponen tidak terhapus permanen, melainkan is_active menjadi false
    $comp->refresh();
    expect($comp->exists)->toBeTrue();
    expect($comp->is_active)->toBeFalse();
});

// ==================== 2. INPUT NILAI & PERHITUNGAN ====================

it('calculates final score accurately and sets is_complete true when all components are filled', function (): void {
    // 3 Komponen: Tugas (40%), PTS (30%), PAS (30%)
    $comp1 = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Tugas',
        'weight' => 40.0,
        'is_active' => true,
    ]);
    $comp2 = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'PTS',
        'weight' => 30.0,
        'is_active' => true,
    ]);
    $comp3 = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'PAS',
        'weight' => 30.0,
        'is_active' => true,
    ]);

    // Student 1 Nilai: Tugas=88, PTS=85, PAS=90 -> 88*0.4 + 85*0.3 + 90*0.3 = 35.2 + 25.5 + 27.0 = 87.70
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.store'), [
            'subject_assignment_id' => $this->assignment->id,
            'scores' => [
                $this->student1->id => [
                    $comp1->id => '88',
                    $comp2->id => '85',
                    $comp3->id => '90',
                ],
            ],
            'descriptions' => [
                $this->student1->id => 'Penguasaan materi sangat baik.',
            ],
        ])
        ->assertRedirect();

    $result = StudentSubjectResult::query()
        ->where('student_id', $this->student1->id)
        ->where('subject_assignment_id', $this->assignment->id)
        ->first();

    expect($result)->not->toBeNull();
    expect((float) $result->final_score)->toEqual(87.70);
    expect($result->is_complete)->toBeTrue();
    expect($result->competency_description)->toEqual('Penguasaan materi sangat baik.');

    $score1 = StudentAssessmentScore::query()
        ->where('student_id', $this->student1->id)
        ->where('assessment_component_id', $comp1->id)
        ->first();
    expect((float) $score1->score)->toEqual(88.0);
});

it('differentiates empty input (null) from zero (0.00) and marks incomplete', function (): void {
    $comp1 = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Tugas',
        'weight' => 50.0,
        'is_active' => true,
    ]);
    $comp2 = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'PTS',
        'weight' => 50.0,
        'is_active' => true,
    ]);

    // Student 1: Comp 1 = 0, Comp 2 = empty ('')
    $this->actingAs($this->admin)
        ->post(route('admin.assessments.store'), [
            'subject_assignment_id' => $this->assignment->id,
            'scores' => [
                $this->student1->id => [
                    $comp1->id => '0',
                    $comp2->id => '',
                ],
            ],
        ])
        ->assertRedirect();

    $scoreComp1 = StudentAssessmentScore::query()
        ->where('student_id', $this->student1->id)
        ->where('assessment_component_id', $comp1->id)
        ->first();
    expect((float) $scoreComp1->score)->toEqual(0.00);

    $scoreComp2 = StudentAssessmentScore::query()
        ->where('student_id', $this->student1->id)
        ->where('assessment_component_id', $comp2->id)
        ->first();
    expect($scoreComp2->score)->toBeNull();

    $result = StudentSubjectResult::query()
        ->where('student_id', $this->student1->id)
        ->where('subject_assignment_id', $this->assignment->id)
        ->first();
    expect($result->is_complete)->toBeFalse();
    expect($result->final_score)->toBeNull();
});

it('rejects scores outside 0 to 100 range', function (): void {
    $comp = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Tugas',
        'weight' => 100.0,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.assessments.store'), [
            'subject_assignment_id' => $this->assignment->id,
            'scores' => [
                $this->student1->id => [
                    $comp->id => '105',
                ],
            ],
        ])
        ->assertSessionHasErrors(["scores.{$this->student1->id}.{$comp->id}"]);
});

// ==================== 3. KEAMANAN & OTORISASI ====================

it('blocks non-admin users from accessing assessment routes', function (): void {
    $this->actingAs($this->wali)
        ->get(route('admin.assessments.components.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.assessments.index'))
        ->assertForbidden();

    $this->actingAs($this->wali)
        ->get(route('admin.assessments.recap'))
        ->assertForbidden();
});

it('aborts with 403 when trying to input grades for student outside the class enrollment', function (): void {
    $otherClass = SchoolClass::factory()->create(['academic_year_id' => $this->year->id, 'name' => '4B']);
    $otherStudent = Student::factory()->create(['name' => 'Siswa Luar']);

    Enrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $otherClass->id,
    ]);

    $comp = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'weight' => 100.0,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.assessments.store'), [
            'subject_assignment_id' => $this->assignment->id,
            'scores' => [
                $otherStudent->id => [
                    $comp->id => '90',
                ],
            ],
        ])
        ->assertForbidden();
});

// ==================== 4. REKAPITULASI & STATISTIK ====================

it('displays accurate summary statistics and recap filters', function (): void {
    $comp = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'weight' => 100.0,
        'is_active' => true,
    ]);

    // Student 1 lengkap (90.00)
    StudentAssessmentScore::factory()->create([
        'student_id' => $this->student1->id,
        'subject_assignment_id' => $this->assignment->id,
        'assessment_component_id' => $comp->id,
        'score' => 90.00,
    ]);
    StudentSubjectResult::factory()->create([
        'student_id' => $this->student1->id,
        'subject_assignment_id' => $this->assignment->id,
        'final_score' => 90.00,
        'is_complete' => true,
    ]);

    // Student 2 belum lengkap
    StudentSubjectResult::factory()->create([
        'student_id' => $this->student2->id,
        'subject_assignment_id' => $this->assignment->id,
        'final_score' => null,
        'is_complete' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.assessments.recap', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
        ]))
        ->assertOk()
        ->assertSee('Rekap Penilaian Akademik')
        ->assertSee('Ahmad Faiz')
        ->assertSee('Bilal Habibi')
        ->assertSee('90.00');

    // Filter 'complete' hanya menampilkan Ahmad Faiz
    $this->actingAs($this->admin)
        ->get(route('admin.assessments.recap', [
            'semester_id' => $this->semester->id,
            'school_class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'status' => 'complete',
        ]))
        ->assertOk()
        ->assertSee('Ahmad Faiz')
        ->assertDontSee('Bilal Habibi');
});

// ==================== 5. INTEGRITAS HISTORIS ANTARSEMESTER ====================

it('preserves assessment scores and components across different semesters', function (): void {
    $semPast = Semester::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Genap Sebelumnya']);
    $assignPast = SubjectAssignment::factory()->create([
        'semester_id' => $semPast->id,
        'school_class_id' => $this->class->id,
        'subject_id' => $this->subject->id,
    ]);

    $compPast = AssessmentComponent::factory()->create([
        'semester_id' => $semPast->id,
        'subject_id' => $this->subject->id,
        'name' => 'Nilai Semester Lalu',
        'weight' => 100.0,
    ]);

    StudentSubjectResult::factory()->create([
        'student_id' => $this->student1->id,
        'subject_assignment_id' => $assignPast->id,
        'final_score' => 82.50,
        'is_complete' => true,
    ]);

    // Tambah nilai di semester baru
    $compNew = AssessmentComponent::factory()->create([
        'semester_id' => $this->semester->id,
        'subject_id' => $this->subject->id,
        'name' => 'Nilai Semester Ini',
        'weight' => 100.0,
    ]);

    StudentSubjectResult::factory()->create([
        'student_id' => $this->student1->id,
        'subject_assignment_id' => $this->assignment->id,
        'final_score' => 95.00,
        'is_complete' => true,
    ]);

    // Verifikasi bahwa nilai di semester lampau tetap utuh
    $pastResult = StudentSubjectResult::query()
        ->where('student_id', $this->student1->id)
        ->where('subject_assignment_id', $assignPast->id)
        ->first();
    expect((float) $pastResult->final_score)->toEqual(82.50);

    $newResult = StudentSubjectResult::query()
        ->where('student_id', $this->student1->id)
        ->where('subject_assignment_id', $this->assignment->id)
        ->first();
    expect((float) $newResult->final_score)->toEqual(95.00);
});
