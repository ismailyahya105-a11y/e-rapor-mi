<?php

namespace Database\Factories;

use App\Models\HomeroomTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeroomTeacherAssignment>
 */
class HomeroomTeacherAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(), 'semester_id' => Semester::factory(),
            'teacher_name' => fake()->name(), 'teacher_nip' => fake()->numerify('##################'),
        ];
    }
}
