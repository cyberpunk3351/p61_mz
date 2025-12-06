<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistResource;
use App\Http\Resources\Track\TrackResource;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Track;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Playlist $playlist): Response
    {
        $tracks = $playlist->tracks()
            ->with(['artists', 'albums'])
            ->select('tracks.*')
            ->addSelect([
                'first_artist_name' => Artist::query()
                    ->select('name')
                    ->join('artist_track', 'artists.id', '=', 'artist_track.artist_id')
                    ->whereColumn('artist_track.track_id', 'tracks.id')
                    ->orderBy('artist_track.artist_id')
                    ->limit(1),
            ])
            ->orderBy('first_artist_name')
            ->paginate(15)
            ->through(static function (Track $track): array {
                return TrackResource::make($track)->resolve();
            });

        return Inertia::render('playlist/ShowPage', [
            'playlist' => [
                'data' => PlaylistResource::make($playlist)->resolve(),
            ],
            'tracks' => [
                'data' => $tracks->items(),        // как и было до ресурса
                'current_page' => $tracks->currentPage(),
                'next_page_url' => $tracks->nextPageUrl(),
            ],
        ]);
    }
}
