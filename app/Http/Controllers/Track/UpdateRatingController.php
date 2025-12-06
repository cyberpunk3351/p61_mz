<?php

declare(strict_types=1);

namespace App\Http\Controllers\Track;

use App\Http\Requests\Track\UpdateRatingRequest;
use App\Models\Track;
use Illuminate\Http\RedirectResponse;

class UpdateRatingController
{
    public function __invoke(UpdateRatingRequest $request, Track $track): RedirectResponse
    {
        $track->update([
            'rating' => $request->integer('rating'),
        ]);

        return back();
    }
}
