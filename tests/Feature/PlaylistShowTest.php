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

    public function test_tracks_can_be_sorted_by_artist(): void
    {
        $user = User::factory()->create();

        $playlist = Playlist::create([
            'title' => 'Sorted Playlist',
            'hash' => Str::uuid()->toString(),
        ]);

        $alpha = Artist::create(['name' => 'Alpha']);
        $bravo = Artist::create(['name' => 'Bravo']);

        $firstTrack = Track::create([
            'title' => 'Second Song',
        ]);

        $secondTrack = Track::create([
            'title' => 'First Song',
        ]);

        $firstTrack->artists()->attach($bravo);
        $secondTrack->artists()->attach($alpha);

        $playlist->tracks()->attach([$firstTrack->id, $secondTrack->id]);

        $response = $this->actingAs($user)->get(route('playlists.show', [
            'playlist' => $playlist,
            'order_by' => 'artist',
        ]));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('playlist/ShowPage')
                ->where('tracks.data.0.id', $secondTrack->id)
                ->where('tracks.data.0.artist', ['Alpha'])
        );
    }

    public function test_track_can_be_detached_from_playlist(): void
    {
        $user = User::factory()->create();

        $playlist = Playlist::create([
            'title' => 'Deletable Playlist',
            'hash' => Str::uuid()->toString(),
        ]);

        $track = Track::create([
            'title' => 'Removable Track',
        ]);

        $playlist->tracks()->attach($track);

        $response = $this
            ->actingAs($user)
            ->delete(route('playlists.tracks.detach', [
                'playlist' => $playlist,
                'track' => $track,
            ]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('playlist_track', [
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
        ]);

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'title' => 'Removable Track',
        ]);
    }
}
