<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\Playlist\PlaylistResource;
use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlaylistResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_playlist_resource_includes_tracks_when_loaded(): void
    {
        $playlist = Playlist::create([
            'title' => 'My Playlist',
            'hash' => Str::uuid()->toString(),
        ]);

        $track = Track::create([
            'title' => 'Track One',
        ]);

        $playlist->tracks()->attach($track);

        $payload = PlaylistResource::make(
            $playlist->load('tracks')
        )->resolve();

        $this->assertArrayHasKey('tracks', $payload);
        $this->assertCount(1, $payload['tracks']);
        $this->assertSame($track->id, $payload['tracks'][0]['id']);
        $this->assertSame($track->title, $payload['tracks'][0]['title']);
    }
}
