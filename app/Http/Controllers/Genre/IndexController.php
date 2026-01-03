<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genre;

use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController
{
    public function __invoke(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = $request->input('limit', 50);

        $genres = Genre::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount('tracks') // To show how many tracks are in this genre
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('genre/IndexPage', [
            'genres' => $genres,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
