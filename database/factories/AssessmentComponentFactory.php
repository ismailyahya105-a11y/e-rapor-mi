<?php

namespace Database\Factories;

use App\Models\AssessmentComponent;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentComponent>
 */
class AssessmentComponentFactory extends Factory
{
    protected $model = AssessmentComponent::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'semester_id' => Semester::factory(),
            'name' => fake()->randomElement(['Tugas', 'Ulangan Harian', 'PTS', 'PAS', 'Praktik']).' '.fake()->unique()->numberBetween(1, 9999),
            'weight' => 25.00,
            'order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
