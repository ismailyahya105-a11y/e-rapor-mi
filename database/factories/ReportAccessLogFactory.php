<?php

namespace Database\Factories;

use App\Models\ReportAccessLog;
use App\Models\ReportPublication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportAccessLog>
 */
class ReportAccessLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_publication_id' => ReportPublication::factory(),
            'user_id' => User::factory()->wali(),
            'action' => 'download',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'downloaded_at' => now(),
        ];
    }
}
