<?php

namespace Database\Factories;

use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentActivity>
 */
class StudentActivityFactory extends Factory
{
    protected $model = StudentActivity::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'date' => fake()->date(),
            'aspect' => fake()->randomElement([
                'Keaktifan Bertanya', 'Partisipasi Diskusi', 'Keterlibatan Kegiatan Kelas',
                'Kerja Sama Kelompok', 'Inisiatif Belajar',
            ]),
            'description' => fake()->sentence(8),
            'teacher_notes' => fake()->optional()->sentence(5),
            'recorded_by_user_id' => User::factory()->admin(),
        ];
    }
}
