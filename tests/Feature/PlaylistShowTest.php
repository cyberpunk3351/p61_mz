<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlaylistShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_playlist_tracks_are_paginated_for_infinite_scroll(): void
    {
        $user = User::factory()->create();

        $playlist = Playlist::create([
            'title' => 'Morning Mix',
            'hash' => Str::uuid()->toString(),
        ]);

        $artist = Artist::create([
            'name' => 'Artist One',
        ]);

        for ($index = 1; $index <= 20; $index++) {
            $track = Track::create([
                'title' => 'Track '.$index,
            ]);

            $track->artists()->attach($artist);
            $playlist->tracks()->attach($track);
        }

        $response = $this->actingAs($user)->get(route('playlists.show', $playlist));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('playlist/ShowPage')
                ->where('playlist.data.id', $playlist->id)
                ->has('tracks.data', 15)
                ->where('tracks.current_page', 1)
                ->where('tracks.next_page_url', static fn (?string $value): bool => $value !== null)
                ->where('tracks.data.0.artist', ['Artist One'])
        );
    }
}
