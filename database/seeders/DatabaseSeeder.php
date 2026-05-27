<?php

namespace Database\Seeders;

use App\Models\InterestOption;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]
        );

        foreach (['Leadership coaching', 'Programs', 'Events', 'Family business'] as $i => $label) {
            InterestOption::query()->firstOrCreate(
                ['label' => $label],
                ['sort_order' => ($i + 1) * 10, 'is_active' => true]
            );
        }
    }
}
