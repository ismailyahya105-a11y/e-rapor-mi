<?php

namespace Database\Factories;

use App\Models\SchoolProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolProfile>
 */
class SchoolProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(), 'npsn' => fake()->numerify('########'),
            'address' => fake()->address(), 'village' => fake()->city(), 'district' => fake()->citySuffix(),
            'regency' => fake()->city(), 'province' => fake()->state(), 'principal_name' => fake()->name(),
        ];
    }
}
