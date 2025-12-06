<?php

declare(strict_types=1);

namespace App\Http\Controllers\Artist;

use App\Http\Resources\Artist\ArtistsResource;
use App\Models\Artist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GetController
{
    public function __invoke(Request $request): Response
    {
        $artists = Artist::query()
            ->withCount(['albums', 'tracks'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $artists->getCollection()->transform(
            static fn (Artist $artist) => ArtistsResource::make($artist)->resolve()
        );

        return Inertia::render('artists/IndexPage', [
            'artists' => [
                'data' => $artists->items(),
                'links' => $artists->linkCollection(),
                'meta' => collect($artists->toArray())->only([
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]),
                'current_page' => $artists->currentPage(),
                'next_page_url' => $artists->nextPageUrl(),
            ],
        ]);
    }
}
