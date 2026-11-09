<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentSubjectResult;
use App\Models\SubjectAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentSubjectResult>
 */
class StudentSubjectResultFactory extends Factory
{
    protected $model = StudentSubjectResult::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_assignment_id' => SubjectAssignment::factory(),
            'final_score' => fake()->randomFloat(2, 70, 98),
            'competency_description' => 'Menunjukkan pemahaman yang baik terhadap materi pembelajaran.',
            'is_complete' => true,
        ];
    }
}
