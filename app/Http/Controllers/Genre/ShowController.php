<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genre;

use App\Http\Resources\Track\TrackResource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Genre $genre, Request $request): Response
    {
        $defaultPerPage = 15;
        
        $perPage = $request->input('limit', $defaultPerPage);

        // Load tracks related to this genre
        $tracks = $genre->tracks()
            ->with(['artists', 'genres']) // Eager load relationships
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return Inertia::render('genre/ShowPage', [
            'genre' => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
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
