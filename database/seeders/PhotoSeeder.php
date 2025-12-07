<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\News;
use App\Models\Activity;
use App\Models\Complaint;
use Illuminate\Database\Seeder;

class PhotoSeeder extends Seeder
{
    public function run(): void
    {
        // Create photos for News
        News::all()->each(function ($news) {
            $news->photos()->saveMany(
                Photo::factory(rand(1, 3))->make([
                    'photoable_type' => News::class,
                    'photoable_id' => $news->id,
                ])
            );
        });

        // Create photos for Activities
        Activity::all()->each(function ($activity) {
            $activity->photos()->saveMany(
                Photo::factory(rand(1, 5))->make([
                    'photoable_type' => Activity::class,
                    'photoable_id' => $activity->id,
                ])
            );
        });

        // Create photos for Complaints
        Complaint::all()->each(function ($complaint) {
            $complaint->photos()->saveMany(
                Photo::factory(rand(0, 2))->make([
                    'photoable_type' => Complaint::class,
                    'photoable_id' => $complaint->id,
                ])
            );
        });
    }
}
