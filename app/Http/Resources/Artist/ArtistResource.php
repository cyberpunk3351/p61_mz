<?php

namespace App\Http\Resources\Artist;

use App\Http\Resources\Album\AlbumResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
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
            'name' => $this->name,
            'albums' => AlbumResource::collection($this->whenLoaded('albums')),
            'date' => $this->created_at->toDateTimeString(),
        ];
    }
}
