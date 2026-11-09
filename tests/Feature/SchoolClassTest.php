<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->year = AcademicYear::factory()->create(['name' => '2025/2026']);
});

it('allows admin to create school classes with custom names for grades 1-6', function (): void {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.school-classes.store'), [
            'academic_year_id' => $this->year->id,
            'grade_level' => 4,
            'name' => '4A',
        ]);

    $response->assertRedirect(route('admin.school-classes.index', ['academic_year_id' => $this->year->id]));

    expect(SchoolClass::query()->where('name', '4A')->where('grade_level', 4)->exists())->toBeTrue();
});

it('validates unique class name within the same academic year', function (): void {
    SchoolClass::factory()->create([
        'academic_year_id' => $this->year->id,
        'grade_level' => 4,
        'name' => '4A',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.school-classes.store'), [
            'academic_year_id' => $this->year->id,
            'grade_level' => 4,
            'name' => '4A',
        ])
        ->assertSessionHasErrors(['name']);
});

it('prevents deleting a class with student enrollments', function (): void {
    $class = SchoolClass::factory()->create([
        'academic_year_id' => $this->year->id,
        'name' => '1A',
        'grade_level' => 1,
    ]);

    $semester = Semester::factory()->create(['academic_year_id' => $this->year->id]);

    Enrollment::factory()->create([
        'school_class_id' => $class->id,
        'semester_id' => $semester->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.school-classes.destroy', $class))
        ->assertSessionHas('error');

    expect(SchoolClass::query()->find($class->id))->not->toBeNull();
});
