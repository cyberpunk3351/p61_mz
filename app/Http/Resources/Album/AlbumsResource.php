<?php

namespace App\Http\Resources\Album;

use App\Http\Resources\Artist\ArtistResource;
use App\Http\Resources\Track\GenresResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumsResource extends JsonResource
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
            'year' => $this->year,
            'rating' => $this->rating,
            'created_at' => $this->created_at->toDateTimeString(),
            //            'tracks' => TrackResource::collection($this->whenLoaded('tracks')),
            //            'artist' => ArtistResource::collection($this->whenLoaded('artists'))->first()?->name,
            //            'artist_id' => ArtistResource::collection($this->whenLoaded('artists'))->first()->id,
        ];
    }
}
