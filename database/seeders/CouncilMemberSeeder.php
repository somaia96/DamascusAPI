<?php

namespace Database\Seeders;

use App\Models\CouncilMember;
use App\Models\Photo;
use Illuminate\Database\Seeder;

class CouncilMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 council members
        CouncilMember::factory()->count(10)->create()->each(function ($councilMember) {
            // Create a photo for each council member
            $photo = new Photo([
                'photoable_type' => CouncilMember::class,
                'photoable_id' => $councilMember->id, // Set the ID of the council member
                'photo_url' => asset('images/default_member_photo.png'), // Use the default photo URL
            ]);
            $councilMember->photo()->save($photo); // Save the photo
        });
    }
}
