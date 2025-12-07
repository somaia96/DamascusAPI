<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Consultation',
            'Maintenance',
            'Installation',
            'Repair',
            'Training',
        ];

        foreach ($categories as $category) {
            ServiceCategory::create(['name' => $category]);
        }
    }
}
