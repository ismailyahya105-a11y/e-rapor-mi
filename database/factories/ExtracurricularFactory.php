<?php

namespace Database\Factories;

use App\Models\Extracurricular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Extracurricular>
 */
class ExtracurricularFactory extends Factory
{
    protected $model = Extracurricular::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Pramuka', 'Seni Baca Al-Qur\'an (Tilawah)', 'Kaligrafi', 'Futsal',
                'Seni Hadroh / Marawis', 'Dokter Kecil / UKS', 'Bulu Tangkis', 'Robotika',
            ]).' '.fake()->unique()->numberBetween(1, 9999),
            'category' => fake()->randomElement(['Keagamaan', 'Seni & Budaya', 'Olahraga', 'Kepanduan', 'Akademik']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
