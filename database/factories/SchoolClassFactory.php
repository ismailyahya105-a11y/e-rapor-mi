<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $grade = fake()->numberBetween(1, 6);
        $section = fake()->randomElement(['A', 'B', 'C']);

        return [
            'academic_year_id' => AcademicYear::factory(),
            'grade_level' => $grade,
            'name' => "{$grade}{$section}",
        ];
    }
}
