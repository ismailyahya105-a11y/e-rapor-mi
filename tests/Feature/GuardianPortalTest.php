<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();

    $this->year = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
    $this->semester = Semester::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Ganjil', 'is_active' => true]);
    $this->class = SchoolClass::factory()->create(['academic_year_id' => $this->year->id, 'name' => '4A', 'grade_level' => 4]);
});

it('allows admin to create a wali account and link with a student', function (): void {
    $student = Student::factory()->create(['name' => 'Faiz']);

    $this->actingAs($this->admin)
        ->post(route('admin.guardians.store'), [
            'name' => 'Pak Hendra',
            'email' => 'hendra@example.test',
            'password' => 'password123',
            'student_id' => $student->id,
            'relationship' => 'Ayah',
        ])
        ->assertRedirect(route('admin.guardians.index'));

    $guardian = User::query()->where('email', 'hendra@example.test')->first();
    expect($guardian)->not->toBeNull();
    expect($guardian->isWali())->toBeTrue();
    expect($guardian->students()->where('students.id', $student->id)->exists())->toBeTrue();
});

it('allows one wali to be linked to multiple children', function (): void {
    $wali = User::factory()->wali()->create();
    $child1 = Student::factory()->create(['name' => 'Kakak']);
    $child2 = Student::factory()->create(['name' => 'Adik']);

    $this->actingAs($this->admin)
        ->post(route('admin.guardians.link-student', $wali), [
            'student_id' => $child1->id,
            'relationship' => 'Ayah',
        ]);

    $this->actingAs($this->admin)
        ->post(route('admin.guardians.link-student', $wali), [
            'student_id' => $child2->id,
            'relationship' => 'Ayah',
        ]);

    expect($wali->students)->toHaveCount(2);
});

it('allows admin to unlink a child from a guardian', function (): void {
    $wali = User::factory()->wali()->create();
    $child = Student::factory()->create();
    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $child->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.guardians.unlink-student', ['guardian' => $wali, 'student' => $child]))
        ->assertRedirect(route('admin.guardians.index'));

    expect($wali->students()->count())->toBe(0);
});

it('shows linked children and their active class on the wali portal dashboard', function (): void {
    $wali = User::factory()->wali()->create();
    $child = Student::factory()->create(['name' => 'Ahmad Faiz Al-Faruq']);

    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $child->id,
        'relationship' => 'Ayah',
    ]);

    Enrollment::factory()->create([
        'student_id' => $child->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
    ]);

    $this->actingAs($wali)
        ->get(route('wali.dashboard'))
        ->assertOk()
        ->assertSee('Ahmad Faiz Al-Faruq')
        ->assertSee('Kelas 4A');
});

it('displays friendly empty state on wali dashboard when no children are linked', function (): void {
    $waliWithoutChildren = User::factory()->wali()->create();

    $this->actingAs($waliWithoutChildren)
        ->get(route('wali.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada data anak yang terhubung');
});

it('allows a wali to view details of their own linked child', function (): void {
    $wali = User::factory()->wali()->create();
    $myChild = Student::factory()->create(['name' => 'Anak Kandung']);

    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $myChild->id,
    ]);

    $this->actingAs($wali)
        ->get(route('wali.children.show', $myChild))
        ->assertOk()
        ->assertSee('Anak Kandung');
});

it('strictly forbids a wali from accessing data of another child (security test: 403 Forbidden)', function (): void {
    $wali = User::factory()->wali()->create();
    $myChild = Student::factory()->create(['name' => 'Anak Kandung']);
    $otherChild = Student::factory()->create(['name' => 'Anak Orang Lain']);

    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $myChild->id,
    ]);

    // Wali tries to access the other child directly via ID or URL
    $this->actingAs($wali)
        ->get(route('wali.children.show', $otherChild))
        ->assertForbidden();
});
