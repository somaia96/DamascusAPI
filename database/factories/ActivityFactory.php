<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'activity_date' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'activity_type_id' => ActivityType::factory(),
        ];
    }

    public function withPhoto()
    {
        return $this->afterCreating(function ($activity) {
            if (rand(0, 1)) {
                $activity->photos()->create([
                    'photoable_type' => Activity::class,
                    'photoable_id' => $activity->id,
                    'photo_url' => asset('images/default_activity_photo.png')
                ]);
            }
        });
    }
}
