<?php

namespace Database\Seeders;

use App\Models\Decision;
use Illuminate\Database\Seeder;

class DecisionSeeder extends Seeder
{
    public function run(): void
    {
        Decision::factory(30)->withPhotos(2)->create(); // Create 30 decisions with 2 photos each
    }
}
