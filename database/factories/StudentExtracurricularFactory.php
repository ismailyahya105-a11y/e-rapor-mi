<?php

namespace Database\Factories;

use App\Models\Extracurricular;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentExtracurricular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentExtracurricular>
 */
class StudentExtracurricularFactory extends Factory
{
    protected $model = StudentExtracurricular::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'extracurricular_id' => Extracurricular::factory(),
            'predicate' => fake()->randomElement(['Sangat Baik', 'Baik', 'Cukup']),
            'description' => fake()->sentence(8),
        ];
    }
}
