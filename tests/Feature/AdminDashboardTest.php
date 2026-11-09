<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create(['name' => 'Pak Kepala Madrasah']);
    $this->wali = User::factory()->wali()->create();

    $this->year = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
    $this->semester = Semester::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Ganjil', 'is_active' => true]);
    $this->class = SchoolClass::factory()->create(['academic_year_id' => $this->year->id, 'name' => '4A', 'grade_level' => 4]);
    $this->subject = Subject::factory()->create(['name' => 'Fikih MI', 'is_active' => true]);
    $this->student = Student::factory()->create(['name' => 'Faiz Al-Faruq', 'status' => 'aktif']);

    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'semester_id' => $this->semester->id,
        'school_class_id' => $this->class->id,
    ]);
});

it('displays real database metrics and active period on admin dashboard', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Pak Kepala Madrasah.')
        ->assertSee('2025/2026 — Semester Ganjil')
        ->assertSee('Faiz Al-Faruq');
});

it('forbids unauthenticated and non-admin users from admin dashboard', function (): void {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->actingAs($this->wali)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
