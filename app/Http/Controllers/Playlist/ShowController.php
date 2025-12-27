<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistResource;
use App\Http\Resources\Track\TrackResource;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Playlist $playlist, Request $request): Response
    {
        $defaultPerPage = 10;
        $maxPerPage = 50;

//        dd($request->all());

        $perPage = $request->input('limit', $defaultPerPage);
        $playlist->loadMissing(['tracks.artists']);

        $tracks = $playlist
            ->tracks()
            ->with('artists')
            ->paginate($perPage);

        return Inertia::render('playlist/ShowPage', [
            'playlist' => [
                'data' => PlaylistResource::make($playlist)->resolve(),
            ],
            'tracks' => [
                'data' => TrackResource::collection($tracks)->resolve(),
                'current_page' => $tracks->currentPage(),
                'next_page_url' => $tracks->nextPageUrl(),
                'per_page' => $defaultPerPage,
                'total' => $tracks->total(),
            ],
        ]);
    }
}
