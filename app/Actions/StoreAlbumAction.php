<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Album;

readonly class StoreAlbumAction
{
    /**
     * @param array<string, string|null> $row
     * @return Album|null
     */
    public function __invoke(array $row): ?Album
    {
        $albumName  = $row['album'] ?? null;
        $releaseDate  = $row['album_date'] ?? null;
        $trackId = $row['spotify_album_id'] ?? null;

        if (! is_string($albumName) || trim($albumName) === '') {
            return null;
        }

        return Album::firstOrCreate(
            [
                'title' => $albumName,
            ],
            [
                'year' => $releaseDate ?? null,
                'spotify_id' => $trackId ?? null,
            ]
        );
    }
}
