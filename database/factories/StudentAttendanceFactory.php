<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendance>
 */
class StudentAttendanceFactory extends Factory
{
    protected $model = StudentAttendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'school_class_id' => SchoolClass::factory(),
            'date' => fake()->date(),
            'status' => fake()->randomElement(['Hadir', 'Sakit', 'Izin', 'Alpa']),
            'notes' => fake()->optional()->sentence(),
            'recorded_by_user_id' => User::factory()->admin(),
        ];
    }
}
