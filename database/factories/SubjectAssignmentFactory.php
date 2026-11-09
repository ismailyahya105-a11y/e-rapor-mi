<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubjectAssignment>
 */
class SubjectAssignmentFactory extends Factory
{
    protected $model = SubjectAssignment::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'semester_id' => Semester::factory(),
            'school_class_id' => function (array $attributes) {
                $semester = isset($attributes['semester_id'])
                    ? Semester::query()->find($attributes['semester_id'])
                    : null;

                $yearId = $semester?->academic_year_id ?? AcademicYear::factory()->create()->id;

                return SchoolClass::factory()->create([
                    'academic_year_id' => $yearId,
                ])->id;
            },
        ];
    }
}
