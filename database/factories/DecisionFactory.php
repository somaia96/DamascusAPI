<?php

namespace Database\Factories;

use App\Models\Decision;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Photo;

class DecisionFactory extends Factory
{
    protected $model = Decision::class;

    public function definition()
    {
        return [
            'decision_id' => $this->faker->unique()->numberBetween(1000, 9999),
            'decision_date' => $this->faker->date(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];
    }

    public function withPhotos($count = 1)
    {
        return $this->afterCreating(function (Decision $decision) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $photo = new Photo([
                    'photoable_type' => Decision::class,
                    'photoable_id' => $decision->id,
                    'photo_url' => asset('images/default_decision_photo.png'), // Use a default photo URL
                ]);
                $decision->photos()->save($photo);
            }
        });
    }
}
