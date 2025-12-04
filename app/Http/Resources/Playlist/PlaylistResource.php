<?php

namespace App\Http\Resources\Playlist;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
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
            'source' => $this->source,
            'date' => $this->created_at->toDateTimeString(),
            'tracks' => $this->transformArtists(),
        ];
    }

    /**
     * Transform tracks data.
     */
    protected function transformArtists(): array
    {
        $transformedTracks = [];
        if ($this->relationLoaded('tracks')) {
            foreach ($this->tracks as $track) {
                $transformedTracks[] = $this->transformTrack($track);
            }
        }

        return $transformedTracks;
    }

    /**
     * Transform single track.
     */
    protected function transformTrack(Track $track): array
    {
        return [
            'id' => $track->id,
            'artist' => $track->artists->pluck('name')->toArray(),
            'release_date' => $track->release_date,
            'rating' => $track->rating,
            'title' => $track->title,
            'genres' => $track->genre,
        ];
    }
}
