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

        $perPage = $request->input('limit', $defaultPerPage);

        // Определяем, нужна ли сортировка по rating
        $sortByRating = $request->has('sort_by') && $request->input('sort_by') === 'rating';

        // Загружаем отношение с сортировкой
        if ($sortByRating) {
            $playlist->loadMissing(['tracks' => function ($query) {
                $query->orderBy('rating', 'desc')->orderBy('tracks.id', 'asc');
            }, 'tracks.artists']);
        } else {
            $playlist->loadMissing(['tracks' => function ($query) {
                $query->orderBy('tracks.id', 'asc');
            }, 'tracks.artists']);
        }

        $tracksQuery = $playlist
            ->tracks()
            ->with('artists');

        if ($sortByRating) {
            $tracksQuery->whereNotNull('rating')->orderBy('rating', 'desc');
        }

        $tracks = $tracksQuery->orderBy('tracks.id', 'asc')->paginate($perPage);

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
