<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'photos' => $this->whenLoaded('photos', function () {
            return $this->photos->map(function ($photo) {
                return $photo->photo_url ?? asset('images/' . basename($photo->getFirstMediaUrl('photos')));
            });
        }),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
}
