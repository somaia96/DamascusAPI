<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition()
    {
        return [
            'photo_url' => $this->faker->imageUrl(),
            'photoable_id' => $this->faker->randomNumber(),
            'photoable_type' => $this->faker->randomElement([
                'App\Models\News',
                'App\Models\Activity',
                'App\Models\Complaint',
            ]),
        ];
    }
}
