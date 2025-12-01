<?php

declare(strict_types=1);

namespace App\Http\Resources\Track;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
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
            'release_date' => $this->release_date,
            'rating' => $this->rating,
            'spotify_id' => $this->spotify_id,
            'isrc' => $this->isrc,
        ];
    }
}
