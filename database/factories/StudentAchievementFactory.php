<?php

namespace Database\Factories;

use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAchievement>
 */
class StudentAchievementFactory extends Factory
{
    protected $model = StudentAchievement::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'achievement_name' => fake()->randomElement([
                'Juara 1 Lomba Tahfiz Juz 30', 'Juara 2 Kaligrafi Tingkat MI',
                'Medali Emas Olimpiade Sains Madrasah', 'Juara 1 Lomba Pidato Bahasa Arab',
                'Juara Harapan 1 Catur Tingkat Kecamatan',
            ]),
            'level' => fake()->randomElement(['Madrasah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional']),
            'activity_type' => fake()->randomElement(['Keagamaan', 'Akademik', 'Seni & Budaya', 'Olahraga']),
            'rank' => fake()->randomElement(['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', 'Medali Emas']),
            'organizer' => fake()->company(),
            'date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
            'supporting_document' => null,
        ];
    }
}
