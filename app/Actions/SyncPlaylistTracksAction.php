<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Playlist;
use App\Models\Track;

class SyncPlaylistTracksAction
{
    public function __invoke(Playlist $playlist, Track $track): bool
    {
        try {
            $playlist->tracks()->save($track);
            return true;
        } catch (Exception $e) {
            // Log error if needed
            return false;
        }
    }
}
