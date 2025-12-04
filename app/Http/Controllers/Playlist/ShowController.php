<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

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
            ->paginate(15)
            ->through(static function (Track $track): array {
                return [
                    'id' => $track->id,
                    'artist' => $track->artists->pluck('name')->toArray(),
                    'release_date' => $track->release_date,
                    'rating' => $track->rating,
                    'title' => $track->title,
                    'genres' => $track->genre,
                ];
            });

        return Inertia::render('playlist/ShowPage', [
            'playlist' => [
                'data' => PlaylistResource::make($playlist)->resolve(),
            ],
            'tracks' => [
                'data' => $tracks->items(),
                'current_page' => $tracks->currentPage(),
                'next_page_url' => $tracks->nextPageUrl(),
            ],
        ]);
    }
}
