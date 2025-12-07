<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ServiceCategory::all();

        foreach ($categories as $category) {
            Service::factory(5)->create([
                'service_category_id' => $category->id,
            ]);
        }
    }
}
