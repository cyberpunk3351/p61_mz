<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Album;
use App\Models\Track;

class SyncAlbumTracksAction
{
    public function __invoke(Album $album, Track $track): bool
    {
        try {
            $album->tracks()->save($track);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
