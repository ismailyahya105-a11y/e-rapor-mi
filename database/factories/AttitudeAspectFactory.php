<?php

namespace Database\Factories;

use App\Models\AttitudeAspect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttitudeAspect>
 */
class AttitudeAspectFactory extends Factory
{
    protected $model = AttitudeAspect::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Kedisiplinan', 'Tanggung jawab', 'Sopan santun', 'Kejujuran',
                'Kerja sama', 'Kemandirian', 'Kepedulian',
            ]).' '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
