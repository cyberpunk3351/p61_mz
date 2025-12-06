<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Album;

class SyncArtistsAlbumsAction
{
    public function __invoke(array $artists, Album $album): bool
    {
        foreach ($artists as $artist) {
            $artist->albums()->syncWithoutDetaching($album);
        }
        return true;
    }
}
