<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Resources\Playlist\PlaylistsResource;
use App\Models\Playlist;
use Inertia\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Playlist $playlist): Response
    {
        dd($playlist);
        return Inertia::render('playlist/IndexPage', [
            'playlist' => PlaylistsResource::collection(Playlist::all())
        ]);
    }
}
