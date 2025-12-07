<?php

declare(strict_types=1);

namespace App\Http\Resources\Track;

use App\Http\Resources\Album\AlbumResource;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Track
 */
class TrackResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Track $track */
        $track = $this;

        return [
            'id' => $track->id,
            'artist' => $track->artists
                ->pluck('name', 'id')
                ->toArray(),
            'artists' => $track->artists
                ->map(static function (Artist $artist): array {
                    return [
                        'id' => $artist->id,
                        'name' => $artist->name,
                    ];
                })
                ->values()
                ->toArray(),
            'release_date' => $track->release_date,
            'rating' => $track->rating,
            'title' => $track->title,
            'albums' => AlbumResource::collection($this->whenLoaded('albums')),
            'genres' => $track->genre,
        ];
    }
}
