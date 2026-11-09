<?php

namespace Database\Factories;

use App\Models\HomeroomNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeroomNote>
 */
class HomeroomNoteFactory extends Factory
{
    protected $model = HomeroomNote::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'school_class_id' => SchoolClass::factory(),
            'academic_progress' => fake()->sentence(8),
            'attitude_behavior' => fake()->sentence(8),
            'discipline' => fake()->sentence(8),
            'student_potential' => fake()->sentence(8),
            'areas_for_improvement' => fake()->sentence(8),
            'parent_advice' => fake()->sentence(8),
            'author_user_id' => User::factory()->admin(),
        ];
    }
}
