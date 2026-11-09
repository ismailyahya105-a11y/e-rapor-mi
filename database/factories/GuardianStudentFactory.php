<?php

namespace Database\Factories;

use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuardianStudent>
 */
class GuardianStudentFactory extends Factory
{
    protected $model = GuardianStudent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->wali(),
            'student_id' => Student::factory(),
            'relationship' => fake()->randomElement(['Ayah', 'Ibu', 'Wali']),
        ];
    }
}
