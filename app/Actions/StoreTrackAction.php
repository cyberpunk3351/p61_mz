<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Track;

readonly class StoreTrackAction
{
    /**
     * @param array<string, string|null> $row
     * @param array $artists
     * @return Track|null
     */
    public function __invoke(array $row): ?Track
    {
        $trackName  = $row['song'] ?? null;
        $releaseDate  = $row['album_date'] ?? null;
        $trackId = $row['spotify_track_id'] ?? null;
        $isrc = $row['i_s_r_c'] ?? null;

        return Track::firstOrCreate(
            [
                'title' => $trackName,
            ],
            [
                'release_date' => $releaseDate ?? null,
                'spotify_id' => $trackId ?? null,
                'isrc' => $isrc ?? null,
            ]
        );
    }
}
