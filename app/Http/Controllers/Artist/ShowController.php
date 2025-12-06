<?php

declare(strict_types=1);

namespace App\Http\Controllers\Artist;

use App\Http\Resources\Artist\ArtistResource;
use App\Http\Resources\Track\TrackResource;
use App\Models\Artist;
use App\Models\Track;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Artist $artist): Response
    {
        $artist->load(['albums'])
            ->loadCount(['albums', 'tracks']);

        $tracks = $artist->tracks()
            ->with(['artists', 'albums'])
            ->orderBy('title')
            ->paginate(15)
            ->through(
                static fn (Track $track): array => TrackResource::make($track)->resolve()
            );

        return Inertia::render('artists/ShowPage', [
            'artist' => [
                'data' => ArtistResource::make($artist)->resolve(),
            ],
            'tracks' => [
                'data' => $tracks->items(),
                'current_page' => $tracks->currentPage(),
                'next_page_url' => $tracks->nextPageUrl(),
            ],
        ]);
    }
}
