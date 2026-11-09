<?php

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Enrollment;
use App\Models\HomeroomTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentSubjectResult;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\User;
use App\Services\ReportDraftBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupClassWithScoresAndStudents(): array
{
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $semester = Semester::factory()->create(['academic_year_id' => $year->id, 'is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'name' => '4A', 'grade_level' => 4]);

    $subject1 = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK-01', 'order' => 1]);
    $subject2 = Subject::factory()->create(['name' => "Al-Qur'an Hadis", 'code' => 'PAI-QH', 'order' => 2]);

    $assignment1 = SubjectAssignment::factory()->create([
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
        'subject_id' => $subject1->id,
    ]);

    $assignment2 = SubjectAssignment::factory()->create([
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
        'subject_id' => $subject2->id,
    ]);

    $studentA = Student::factory()->create(['name' => 'Ahmad Faiz', 'nis' => '0101']);
    $studentB = Student::factory()->create(['name' => 'Bilal Habibi', 'nis' => '0102']);

    Enrollment::factory()->create([
        'student_id' => $studentA->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    Enrollment::factory()->create([
        'student_id' => $studentB->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    // Student A: MTK=90, PAI=95 (Rata-rata 92.5)
    StudentSubjectResult::factory()->create([
        'student_id' => $studentA->id,
        'subject_assignment_id' => $assignment1->id,
        'final_score' => 90,
    ]);
    StudentSubjectResult::factory()->create([
        'student_id' => $studentA->id,
        'subject_assignment_id' => $assignment2->id,
        'final_score' => 95,
    ]);

    // Student B: MTK=80, PAI=84 (Rata-rata 82.0)
    StudentSubjectResult::factory()->create([
        'student_id' => $studentB->id,
        'subject_assignment_id' => $assignment1->id,
        'final_score' => 80,
    ]);
    StudentSubjectResult::factory()->create([
        'student_id' => $studentB->id,
        'subject_assignment_id' => $assignment2->id,
        'final_score' => 84,
    ]);

    // Absensi
    StudentAttendance::factory()->create([
        'student_id' => $studentA->id,
        'semester_id' => $semester->id,
        'status' => 'Sakit',
    ]);

    HomeroomTeacherAssignment::factory()->create([
        'school_class_id' => $class->id,
        'semester_id' => $semester->id,
        'teacher_name' => 'Ustadzah Siti Rahmawati, S.Pd.I.',
    ]);

    return compact('admin', 'year', 'semester', 'class', 'subject1', 'subject2', 'assignment1', 'assignment2', 'studentA', 'studentB');
}

it('displays class ledger table accurately with students, subjects, averages, and rankings', function (): void {
    $data = setupClassWithScoresAndStudents();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.leger.index', [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
            'show_ranking' => 1,
        ]));

    $response->assertOk()
        ->assertSee('Buku Leger Nilai Kelas')
        ->assertSee('Ahmad Faiz')
        ->assertSee('Bilal Habibi')
        ->assertSee('MTK-01')
        ->assertSee('PAI-QH')
        ->assertSee('90')
        ->assertSee('95')
        ->assertSee('80')
        ->assertSee('84')
        ->assertSee('92.5')
        ->assertSee('82.0')
        ->assertSee('Ustadzah Siti Rahmawati');
});

it('exports class ledger to CSV format successfully for authorized admin', function (): void {
    $data = setupClassWithScoresAndStudents();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.leger.export', [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
        ]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment; filename=leger-nilai-kelas-');

    $content = $response->streamedContent();
    expect($content)->toContain('BUKU LEGER NILAI PESERTA DIDIK')
        ->and($content)->toContain('Ahmad Faiz')
        ->and($content)->toContain('Bilal Habibi')
        ->and($content)->toContain('Matematika')
        ->and($content)->toContain('92.5')
        ->and($content)->toContain('82');
});

it('renders printer-friendly print view for class ledger', function (): void {
    $data = setupClassWithScoresAndStudents();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.leger.print', [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
        ]));

    $response->assertOk()
        ->assertSee('Kementerian Agama')
        ->assertSee('Ahmad Faiz')
        ->assertSee('Bilal Habibi')
        ->assertSee('Cetak / Print Dokumen');
});

it('forbids guardians from accessing class ledger and export routes', function (): void {
    $data = setupClassWithScoresAndStudents();
    $wali = User::factory()->wali()->create();

    $this->actingAs($wali)
        ->get(route('admin.leger.index'))
        ->assertForbidden();

    $this->actingAs($wali)
        ->get(route('admin.leger.export', [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
        ]))
        ->assertForbidden();

    $this->actingAs($wali)
        ->get(route('admin.leger.print', [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
        ]))
        ->assertForbidden();
});

it('redirects unauthenticated guests to login on ledger routes', function (): void {
    $this->get(route('admin.leger.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.leger.export', ['semester_id' => 1, 'school_class_id' => 1]))
        ->assertRedirect('/login');
});

it('allows admin to lock and unlock a semester', function (): void {
    $data = setupClassWithScoresAndStudents();

    // 1. Lock semester
    $responseLock = $this->actingAs($data['admin'])
        ->post(route('admin.semesters.lock', $data['semester']));

    $responseLock->assertSessionHas('status');
    expect($data['semester']->fresh()->isLocked())->toBeTrue()
        ->and($data['semester']->fresh()->locked_by_user_id)->toBe($data['admin']->id);

    // 2. Unlock semester
    $responseUnlock = $this->actingAs($data['admin'])
        ->post(route('admin.semesters.unlock', $data['semester']));

    $responseUnlock->assertSessionHas('status');
    expect($data['semester']->fresh()->isLocked())->toBeFalse();
});

it('prevents modifying academic scores when semester is locked', function (): void {
    $data = setupClassWithScoresAndStudents();

    AssessmentComponent::factory()->create([
        'subject_id' => $data['subject1']->id,
        'semester_id' => $data['semester']->id,
        'weight' => 100,
        'is_active' => true,
    ]);

    // Lock semester
    $data['semester']->lock($data['admin']);

    $response = $this->actingAs($data['admin'])
        ->post(route('admin.assessments.store'), [
            'subject_assignment_id' => $data['assignment1']->id,
            'scores' => [
                $data['studentA']->id => [1 => 95],
            ],
        ]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Semester ini telah dikunci/diarsipkan');
});

it('prevents refreshing or publishing report draft when semester is locked', function (): void {
    $data = setupClassWithScoresAndStudents();

    // Buat draf awal
    $draft = app(ReportDraftBuilder::class)->refresh($data['studentA'], $data['semester'], $data['admin']);

    // Lock semester
    $data['semester']->lock($data['admin']);

    // Attempt refresh draft
    $responseRefresh = $this->actingAs($data['admin'])
        ->post(route('admin.report-drafts.refresh', [
            'student' => $data['studentA']->id,
            'semester_id' => $data['semester']->id,
        ]));

    $responseRefresh->assertSessionHas('error');
    expect(session('error'))->toContain('Semester ini telah dikunci/diarsipkan');

    // Attempt publish draft
    $responsePublish = $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $draft));

    $responsePublish->assertSessionHas('error');
    expect(session('error'))->toContain('Semester ini telah dikunci/diarsipkan');
});

it('prevents modifying homeroom notes when semester is locked', function (): void {
    $data = setupClassWithScoresAndStudents();

    // Lock semester
    $data['semester']->lock($data['admin']);

    $response = $this->actingAs($data['admin'])
        ->post(route('admin.homeroom-notes.store'), [
            'semester_id' => $data['semester']->id,
            'school_class_id' => $data['class']->id,
            'notes' => [
                $data['studentA']->id => [
                    'academic_progress' => 'Catatan baru setelah dikunci',
                ],
            ],
        ]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Semester ini telah dikunci/diarsipkan');
});
