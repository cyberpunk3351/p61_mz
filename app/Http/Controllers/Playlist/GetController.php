<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistsResource;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GetController
{
    public function __invoke(Request $request): Response
    {
        $playlists = Playlist::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $playlists->getCollection()->transform(
            static fn (Playlist $playlist) => PlaylistsResource::make($playlist)->resolve()
        );

        return Inertia::render('playlist/IndexPage', [
            'playlists' => [
                'data' => $playlists->items(),
                'links' => $playlists->linkCollection(),
                'meta' => collect($playlists->toArray())->only([
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]),
            ],
        ]);
    }
}
