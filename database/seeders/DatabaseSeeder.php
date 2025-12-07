<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            ServiceCategorySeeder::class,
            ActivityTypeSeeder::class,
            ServiceSeeder::class,
            NewsSeeder::class,
            ActivitySeeder::class,
            DecisionSeeder::class,
            ComplaintSeeder::class,
            CouncilMemberSeeder::class,
            // PhotoSeeder::class, // Add this line

        ]);
    }
}
