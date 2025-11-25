<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Track;

class SyncArtistsTracksAction
{
    public function __invoke(array $artists, Track $track): bool
    {
        foreach ($artists as $artist) {
            $artist->tracks()->syncWithoutDetaching($track);
        }
        return true;
    }
}
