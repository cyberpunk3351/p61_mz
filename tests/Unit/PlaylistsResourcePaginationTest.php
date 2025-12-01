<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\Playlist\PlaylistsResource;
use App\Models\Playlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlaylistsResourcePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_includes_pagination_meta(): void
    {
        foreach (range(1, 3) as $index) {
            Playlist::create([
                'title' => "Playlist {$index}",
                'hash' => Str::uuid()->toString(),
            ]);
        }

        $paginator = Playlist::paginate(2);

        $paginator->getCollection()->transform(
            static fn (Playlist $playlist) => PlaylistsResource::make($playlist)->resolve()
        );

        $payload = [
            'data' => $paginator->items(),
            'links' => $paginator->linkCollection(),
            'meta' => collect($paginator->toArray())->only([
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]),
        ];

        $this->assertCount(2, $payload['data']);
        $this->assertSame(1, $payload['meta']['current_page']);
        $this->assertSame(2, $payload['meta']['per_page']);
        $this->assertSame(2, $payload['meta']['last_page']);
        $this->assertNotEmpty($payload['links']);
    }
}
