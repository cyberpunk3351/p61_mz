<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Track;

class SyncTrackGenresAction
{
    /**
     * @param Track $track
     * @param array<int> $genreIds
     */
    public function __invoke(Track $track, array $genreIds): void
    {
        if ($genreIds === []) {
            return;
        }

        $track->genres()->syncWithoutDetaching($genreIds);
    }
}
