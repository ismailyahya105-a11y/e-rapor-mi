<?php

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
});

it('preserves leading zeros in NIS and NISN when storing student data', function (): void {
    $this->actingAs($this->admin)
        ->post(route('admin.students.store'), [
            'nis' => '00123',
            'nisn' => '0089123456',
            'name' => 'Fatimah Az-Zahra',
            'gender' => 'Perempuan',
            'birth_place' => 'Jakarta',
            'birth_date' => '2016-05-10',
            'address' => 'Jl. Tebet Barat No. 5',
            'status' => 'aktif',
        ])
        ->assertSessionHasNoErrors();

    $student = Student::query()->where('name', 'Fatimah Az-Zahra')->first();
    expect($student)->not->toBeNull();
    expect($student->nis)->toBe('00123');
    expect($student->nisn)->toBe('0089123456');
});

it('rejects duplicate NIS with an Indonesian validation error', function (): void {
    Student::factory()->create(['nis' => '0101']);

    $this->actingAs($this->admin)
        ->post(route('admin.students.store'), [
            'nis' => '0101',
            'name' => 'Siswa Baru',
            'gender' => 'Laki-laki',
            'status' => 'aktif',
        ])
        ->assertSessionHasErrors([
            'nis' => 'NIS sudah digunakan oleh siswa lain.',
        ]);
});

it('rejects duplicate filled NISN but allows multiple students without NISN', function (): void {
    Student::factory()->create(['nis' => '0101', 'nisn' => '0011223344']);

    // Duplicate NISN rejected
    $this->actingAs($this->admin)
        ->post(route('admin.students.store'), [
            'nis' => '0102',
            'nisn' => '0011223344',
            'name' => 'Siswa Kedua',
            'gender' => 'Perempuan',
            'status' => 'aktif',
        ])
        ->assertSessionHasErrors(['nisn']);

    // Null NISN allowed for multiple students
    $this->actingAs($this->admin)
        ->post(route('admin.students.store'), [
            'nis' => '0103',
            'nisn' => null,
            'name' => 'Siswa Tanpa NISN 1',
            'gender' => 'Laki-laki',
            'status' => 'aktif',
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->post(route('admin.students.store'), [
            'nis' => '0104',
            'nisn' => null,
            'name' => 'Siswa Tanpa NISN 2',
            'gender' => 'Perempuan',
            'status' => 'aktif',
        ])
        ->assertSessionHasNoErrors();

    expect(Student::query()->whereNull('nisn')->count())->toBe(2);
});

it('prevents deleting student if enrollment or guardian history exists', function (): void {
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.students.destroy', $student))
        ->assertSessionHas('error');

    expect(Student::query()->find($student->id))->not->toBeNull();
});
