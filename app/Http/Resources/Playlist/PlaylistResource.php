<?php

namespace App\Http\Resources\Playlist;

use App\Http\Resources\Track\TrackResource;
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
        if ($this->tracks) {
            foreach ($this->tracks as $index => $track) {
                $artists = $track->load('artists')->artists->pluck('name')->toArray();
                $transformedTracks[] = [
                    'id' => $track->id,
                    'artist' => $artists,
                    'release_date' => $track->release_date,
                    'rating' => $track->rating,
                    'title' => $track['title'],
                    'genres' => $track['genre'],
                ];
            }
        }

        return $transformedTracks;
    }
}
