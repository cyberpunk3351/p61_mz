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
            dump(true);
            return true;
        } catch (Exception $e) {
            dump(false);
            // Log error if needed
            return false;
        }
    }
}
