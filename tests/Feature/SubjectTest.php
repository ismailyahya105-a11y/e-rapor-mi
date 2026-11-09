<?php

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();

    $this->year = AcademicYear::factory()->create(['name' => '2025/2026']);
    $this->sem = Semester::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Ganjil']);
    $this->class = SchoolClass::factory()->create(['academic_year_id' => $this->year->id, 'name' => '4A', 'grade_level' => 4]);
});

it('allows admin to create subjects with unique codes', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.subjects.store'), [
            'code' => 'MTK-01',
            'name' => 'Matematika',
            'order' => 1,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.subjects.index'));

    expect(Subject::query()->where('code', 'MTK-01')->exists())->toBeTrue();

    // Duplicate code rejected
    $this->actingAs($this->admin)
        ->post(route('admin.subjects.store'), [
            'code' => 'MTK-01',
            'name' => 'Matematika Peminatan',
        ])
        ->assertSessionHasErrors(['code']);
});

it('allows assigning subjects to a class and semester', function (): void {
    $subject1 = Subject::factory()->create(['code' => 'PAI-01']);
    $subject2 = Subject::factory()->create(['code' => 'BIN-01']);

    $this->actingAs($this->admin)
        ->post(route('admin.subject-assignments.store'), [
            'semester_id' => $this->sem->id,
            'school_class_id' => $this->class->id,
            'subject_ids' => [$subject1->id, $subject2->id],
        ])
        ->assertRedirect();

    expect(SubjectAssignment::query()->where('school_class_id', $this->class->id)->count())->toBe(2);
});

it('deactivates subject instead of destroying it if assignments exist', function (): void {
    $subject = Subject::factory()->create(['code' => 'IPA-01', 'is_active' => true]);
    SubjectAssignment::factory()->create([
        'subject_id' => $subject->id,
        'school_class_id' => $this->class->id,
        'semester_id' => $this->sem->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.subjects.destroy', $subject))
        ->assertRedirect(route('admin.subjects.index'));

    expect(Subject::query()->find($subject->id))->not->toBeNull();
    expect($subject->fresh()->is_active)->toBeFalse();
});
