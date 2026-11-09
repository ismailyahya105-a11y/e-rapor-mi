<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
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
            'notes' => null,
        ];
    }
}
