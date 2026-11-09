<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\ReportDraft;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportDraft>
 */
class ReportDraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'enrollment_id' => Enrollment::factory(),
            'snapshot' => ['student' => ['name' => fake()->name()]],
            'source_fingerprint' => hash('sha256', fake()->uuid()),
        ];
    }
}
