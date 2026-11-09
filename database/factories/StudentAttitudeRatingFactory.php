<?php

namespace Database\Factories;

use App\Models\AttitudeAspect;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttitudeRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttitudeRating>
 */
class StudentAttitudeRatingFactory extends Factory
{
    protected $model = StudentAttitudeRating::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'attitude_aspect_id' => AttitudeAspect::factory(),
            'rating' => fake()->randomElement(['Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan']),
            'development_notes' => fake()->sentence(),
            'assessed_by_user_id' => User::factory()->admin(),
        ];
    }
}
