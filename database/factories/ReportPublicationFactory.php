<?php

namespace Database\Factories;

use App\Models\ReportDraft;
use App\Models\ReportPublication;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportPublication>
 */
class ReportPublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_draft_id' => ReportDraft::factory(),
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'published_by_user_id' => User::factory()->admin(),
            'snapshot' => ['student' => ['name' => 'Fulan']],
            'pdf_path' => 'reports/test.pdf',
            'published_at' => now(),
        ];
    }
}
