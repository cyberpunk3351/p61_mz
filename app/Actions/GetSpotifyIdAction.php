<?php

declare(strict_types=1);

namespace App\Actions;

class GetSpotifyIdAction
{
    /**
     * Extract Spotify ID from URI like "spotify:track:5P4qVgVSQuIRwGd1XxgUKJ".
     */
    public function __invoke(string $uri): ?string
    {
        $pos = strrpos($uri, ':');

        if ($pos === false) {
            return null;
        }

        $id = substr($uri, $pos + 1);

        return $id !== '' ? $id : null;
    }

}
