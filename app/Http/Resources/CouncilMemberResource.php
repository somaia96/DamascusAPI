<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'job_title' => $this->job_title, // Include job_title
            'description' => $this->description, // Include description
            'photo' => $this->whenLoaded('photo', function () {
                return $this->photo ? $this->photo->photo_url : null;
            }),
        ];
    }
}
