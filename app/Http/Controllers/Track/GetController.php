<?php

declare(strict_types=1);

namespace App\Http\Controllers\Track;

use App\Http\Requests\Track\SearchRequest;
use App\Http\Resources\Track\TrackResource;
use App\Models\Track;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class GetController
{
    public function __invoke(SearchRequest $request): Response
    {
        $query = $request->validated('query');

        $tracks = Track::search($query ?? '')
            ->query(static function (Builder $builder): void {
                $builder->with(['artists', 'albums']);
            })
            ->paginate(15)
            ->withQueryString();

        $tracks->getCollection()->transform(
            static fn (Track $track) => TrackResource::make($track)->resolve()
        );

        return Inertia::render('tracks/IndexPage', [
            'tracks' => [
                'data' => $tracks->items(),
                'links' => $tracks->linkCollection(),
                'meta' => collect($tracks->toArray())->only([
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]),
            ],
            'filters' => [
                'query' => $query,
            ],
        ]);
    }
}
