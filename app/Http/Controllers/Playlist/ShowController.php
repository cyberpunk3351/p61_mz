<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistResource;
use App\Models\Playlist;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Playlist $playlist): Response
    {
        $playlist->load(['tracks' => function ($query) {
            $query->with('artists')->paginate(15);
        }]);

        return Inertia::render('playlist/ShowPage', [
            'playlist' => PlaylistResource::make($playlist),
        ]);
    }
}
