<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\ReportDraft;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Services\ReportCompletenessChecker;
use App\Services\ReportDraftBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows only one draft for a student and semester', function (): void {
    $year = AcademicYear::factory()->create();
    $semester = Semester::factory()->create(['academic_year_id' => $year->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    ReportDraft::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'enrollment_id' => $enrollment->id,
    ]);

    expect(ReportDraft::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->count())
        ->toBe(1);
});

it('keeps snapshot data unchanged until an admin refreshes it', function (): void {
    $year = AcademicYear::factory()->create();
    $semester = Semester::factory()->create(['academic_year_id' => $year->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create(['name' => 'Nama Awal']);
    Enrollment::factory()->create(['student_id' => $student->id, 'semester_id' => $semester->id, 'school_class_id' => $class->id]);

    $draft = app(ReportDraftBuilder::class)->refresh($student, $semester, User::factory()->admin()->create());
    $student->update(['name' => 'Nama Baru']);

    expect($draft->snapshot['student']['name'])->toBe('Nama Awal')
        ->and($draft->fresh()->snapshot['student']['name'])->toBe('Nama Awal');
});

it('forbids guardians from opening report draft management', function (): void {
    $this->actingAs(User::factory()->wali()->create())
        ->get('/admin/pengelolaan-rapor')
        ->assertForbidden();
});

it('marks a draft as changed when its source data changes', function (): void {
    $year = AcademicYear::factory()->create();
    $semester = Semester::factory()->create(['academic_year_id' => $year->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id, 'semester_id' => $semester->id, 'school_class_id' => $class->id]);
    $draft = app(ReportDraftBuilder::class)->refresh($student, $semester, User::factory()->admin()->create());

    $student->update(['address' => 'Alamat terbaru']);

    expect(app(ReportCompletenessChecker::class)->check($student, $semester, $draft)['source_has_changed'])->toBeTrue();
});
