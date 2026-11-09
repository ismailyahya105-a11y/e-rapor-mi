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
    $this->wali = User::factory()->wali()->create();
});

it('allows admin to view academic years index', function (): void {
    AcademicYear::factory()->create(['name' => '2024/2025']);

    $this->actingAs($this->admin)
        ->get(route('admin.academic-years.index'))
        ->assertOk()
        ->assertSee('2024/2025');
});

it('forbids wali from managing academic years', function (): void {
    $this->actingAs($this->wali)
        ->get(route('admin.academic-years.index'))
        ->assertForbidden();
});

it('automatically creates Ganjil and Genap semesters when a new academic year is created', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.academic-years.store'), [
            'name' => '2026/2027',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.academic-years.index'));

    $year = AcademicYear::query()->where('name', '2026/2027')->first();
    expect($year)->not->toBeNull();
    expect($year->semesters)->toHaveCount(2);

    $ganjil = $year->semesters()->where('name', 'Ganjil')->first();
    $genap = $year->semesters()->where('name', 'Genap')->first();

    expect($ganjil)->not->toBeNull();
    expect($genap)->not->toBeNull();
    expect($ganjil->is_active)->toBeTrue();
    expect($genap->is_active)->toBeFalse();
});

it('ensures only one semester is active across the system at any given time', function (): void {
    $year1 = AcademicYear::factory()->create(['name' => '2024/2025', 'is_active' => true]);
    $sem1 = Semester::factory()->create(['academic_year_id' => $year1->id, 'name' => 'Ganjil', 'is_active' => true]);

    $year2 = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => false]);
    $sem2 = Semester::factory()->create(['academic_year_id' => $year2->id, 'name' => 'Ganjil', 'is_active' => false]);

    // Activate semester 2
    $this->actingAs($this->admin)
        ->post(route('admin.semesters.activate', $sem2))
        ->assertSessionHas('status');

    expect($sem1->fresh()->is_active)->toBeFalse();
    expect($year1->fresh()->is_active)->toBeFalse();
    expect($sem2->fresh()->is_active)->toBeTrue();
    expect($year2->fresh()->is_active)->toBeTrue();
});

it('prevents deleting an academic year that has classes or student enrollments', function (): void {
    $year = AcademicYear::factory()->create(['name' => '2024/2025']);
    $sem = Semester::factory()->create(['academic_year_id' => $year->id, 'name' => 'Ganjil']);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    Enrollment::factory()->create([
        'semester_id' => $sem->id,
        'school_class_id' => $class->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.academic-years.destroy', $year))
        ->assertRedirect(route('admin.academic-years.index'))
        ->assertSessionHas('error');

    expect(AcademicYear::query()->find($year->id))->not->toBeNull();
});
