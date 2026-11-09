<?php

namespace Database\Factories;

use App\Models\AssessmentComponent;
use App\Models\Student;
use App\Models\StudentAssessmentScore;
use App\Models\SubjectAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAssessmentScore>
 */
class StudentAssessmentScoreFactory extends Factory
{
    protected $model = StudentAssessmentScore::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_assignment_id' => SubjectAssignment::factory(),
            'assessment_component_id' => AssessmentComponent::factory(),
            'score' => fake()->randomFloat(2, 60, 95),
        ];
    }
}
