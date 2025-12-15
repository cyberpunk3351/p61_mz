<?php

declare(strict_types=1);

namespace App\Http\Controllers\Playlist;

use App\Http\Requests\Playlist\DetachTrackRequest;
use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\RedirectResponse;

class DetachTrackController
{
    public function __invoke(DetachTrackRequest $request, Playlist $playlist, Track $track): RedirectResponse
    {
        $playlist->tracks()->detach($track->getKey());

        return back();
    }
}
