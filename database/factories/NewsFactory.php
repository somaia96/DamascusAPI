<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];
    }

    public function withPhoto()
    {
        return $this->afterCreating(function ($news) {
            if (rand(0, 1)) {
                $news->photos()->create([
                    'photoable_type' => News::class,
                    'photoable_id' => $news->id,
                    'photo_url' => asset('images/default_news_photo.png')
                ]);
            }
        });
    }
}
