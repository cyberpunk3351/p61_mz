<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistResource;
use App\Http\Resources\Playlist\PlaylistsResource;
use App\Http\Resources\Track\TrackResource;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController
{
    public function __invoke(Request $request, Playlist $playlist): Response
    {
        $defaultPerPage = 21;
        $maxPerPage = 50;

        $perPage = $request->input('limit', $defaultPerPage);
        $query = $request->input('query');

        if (!$query) {
            $tracks = $playlist->tracks()->paginate($perPage);
            return new PlaylistResource($playlist, $tracks);
        }
        $query = strtolower($request->input('query'));

        $tracks = $playlist->tracks()
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$query}%"])
                    ->orWhereHas('artists', function ($artistQuery) use ($query) {
                        $artistQuery->whereRaw('LOWER(name) LIKE ?', ["%{$query}%"]);
                    });
            })
            ->with('artists')
            ->orderByDesc('id')
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
