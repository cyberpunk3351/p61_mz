<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Track\TrackResource;
use App\Http\Resources\Playlist\PlaylistResource;
use App\Models\Track;
use App\Models\Playlist;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Playlist $playlist): Response
    {
        $tracks = $playlist->tracks()
            ->with('artists')
            ->withMin('artists as sort_artist_name', 'name')
            ->orderBy('sort_artist_name')
            ->paginate(15)
            ->through(static function (Track $track): array {
                return TrackResource::make($track)->resolve();
            });

        return Inertia::render('playlist/ShowPage', [
            'playlist' => [
                'data' => PlaylistResource::make($playlist)->resolve(),
            ],
            'tracks' => [
                'data'         => $tracks->items(),
                'current_page' => $tracks->currentPage(),
                'next_page_url'=> $tracks->nextPageUrl(),
            ],
        ]);
    }
}
