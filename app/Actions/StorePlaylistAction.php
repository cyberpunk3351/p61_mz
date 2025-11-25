<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Playlist;

class StorePlaylistAction
{
    /**
     * @param string $name
     * @param string $hash
     * @param string|null $type
     * @return Playlist
     */
    public function __invoke(
        string $name,
        string $hash,
        ?string $type,
    ): Playlist
    {
        $playlist = Playlist::firstOrCreate([
            'title' => $name,
            'hash' => $hash,
        ]);
        $playlist->file()->firstOrCreate([
            'hash' => $hash,
        ], [
            'title' => trim($name),
            'type' => $type,
        ]);

        return $playlist;
    }
}
