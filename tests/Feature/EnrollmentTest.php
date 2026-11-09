<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();

    $this->year1 = AcademicYear::factory()->create(['name' => '2024/2025']);
    $this->sem1Genap = Semester::factory()->create(['academic_year_id' => $this->year1->id, 'name' => 'Genap']);
    $this->class3A = SchoolClass::factory()->create(['academic_year_id' => $this->year1->id, 'name' => '3A', 'grade_level' => 3]);

    $this->year2 = AcademicYear::factory()->create(['name' => '2025/2026']);
    $this->sem2Ganjil = Semester::factory()->create(['academic_year_id' => $this->year2->id, 'name' => 'Ganjil']);
    $this->class4A = SchoolClass::factory()->create(['academic_year_id' => $this->year2->id, 'name' => '4A', 'grade_level' => 4]);
    $this->class4B = SchoolClass::factory()->create(['academic_year_id' => $this->year2->id, 'name' => '4B', 'grade_level' => 4]);
});

it('places a student into a class for a semester', function (): void {
    $student = Student::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.enrollments.store'), [
            'semester_id' => $this->sem2Ganjil->id,
            'school_class_id' => $this->class4A->id,
            'student_id' => $student->id,
        ])
        ->assertRedirect();

    expect(Enrollment::query()->where('student_id', $student->id)->where('semester_id', $this->sem2Ganjil->id)->exists())->toBeTrue();
});

it('rejects placement if class academic year does not match semester academic year', function (): void {
    $student = Student::factory()->create();

    // Trying to place into Class 3A (2024/2025) under Semester 2025/2026 Ganjil
    $this->actingAs($this->admin)
        ->post(route('admin.enrollments.store'), [
            'semester_id' => $this->sem2Ganjil->id,
            'school_class_id' => $this->class3A->id,
            'student_id' => $student->id,
        ])
        ->assertSessionHasErrors(['school_class_id']);
});

it('prevents a student from having multiple class placements in the same semester', function (): void {
    $student = Student::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $this->sem2Ganjil->id,
        'school_class_id' => $this->class4A->id,
    ]);

    // Attempt second placement in same semester
    $this->actingAs($this->admin)
        ->post(route('admin.enrollments.store'), [
            'semester_id' => $this->sem2Ganjil->id,
            'school_class_id' => $this->class4B->id,
            'student_id' => $student->id,
        ])
        ->assertSessionHasErrors(['student_id']);
});

it('preserves multi-semester history when placing student into a new semester', function (): void {
    $student = Student::factory()->create();

    // Past semester enrollment in 3A
    $pastEnrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $this->sem1Genap->id,
        'school_class_id' => $this->class3A->id,
    ]);

    // Current semester placement in 4A
    $this->actingAs($this->admin)
        ->post(route('admin.enrollments.store'), [
            'semester_id' => $this->sem2Ganjil->id,
            'school_class_id' => $this->class4A->id,
            'student_id' => $student->id,
        ])
        ->assertSessionHasNoErrors();

    // Both historical record and new record exist
    expect($student->enrollments()->count())->toBe(2);
    expect($pastEnrollment->fresh()->school_class_id)->toBe($this->class3A->id);
    expect($student->currentClass($this->sem2Ganjil)?->id)->toBe($this->class4A->id);
    expect($student->currentClass($this->sem1Genap)?->id)->toBe($this->class3A->id);
});

it('allows moving a student to a different class within the same semester', function (): void {
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $this->sem2Ganjil->id,
        'school_class_id' => $this->class4A->id,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.enrollments.update', $enrollment), [
            'school_class_id' => $this->class4B->id,
            'notes' => 'Pindah rombel B',
        ])
        ->assertRedirect();

    expect($enrollment->fresh()->school_class_id)->toBe($this->class4B->id);
});
