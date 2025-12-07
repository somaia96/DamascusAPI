<?php

namespace Database\Factories;

use App\Models\CouncilMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouncilMemberFactory extends Factory
{
    protected $model = CouncilMember::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'job_title' => $this->faker->jobTitle, // Generate a random job title
            'description' => $this->faker->paragraph, // Generate a random description
            'photo_id' => null, // Assuming you want to set this later
        ];
    }
}
