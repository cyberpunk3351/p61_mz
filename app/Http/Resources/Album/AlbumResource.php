<?php

namespace App\Http\Resources\Album;

use App\Http\Resources\Artist\ArtistResource;
use App\Http\Resources\Track\GenresResource;
use App\Http\Resources\Track\TrackResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
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
            'spotify_id' => $this->spotify_id,
            'isrc' => $this->isrc,
            'created_at' => $this->created_at->toDateTimeString(),
            'tracks' => TrackResource::collection($this->whenLoaded('tracks')),
            'img_640_url' => $this->img_640_url,
            'img_300_url' => $this->img_300_url,
            'img_64_url' => $this->img_64_url,
            'label' => $this->label,
            'artist' => $this->relationLoaded('artists') && $this->artists->isNotEmpty()
                ? new ArtistResource($this->artists->first())
                : null,
            //            'artist_id' => ArtistResource::collection($this->whenLoaded('artists'))->first()->id,
        ];
    }
}
