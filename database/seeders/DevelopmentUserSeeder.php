<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@raportku.test'],
            [
                'name' => 'Admin RAPORTKU',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'wali@raportku.test'],
            [
                'name' => 'Wali Demo',
                'role' => 'wali',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
    }
}
