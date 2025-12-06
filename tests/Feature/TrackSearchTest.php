<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrackSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_search_tracks(): void
    {
        config(['scout.driver' => 'collection']);

        $user = User::factory()->create();

        $artist = Artist::create([
            'name' => 'Searchable Artist',
        ]);

        $matchingTrack = Track::create([
            'title' => 'Searchable Song',
        ]);

        $matchingTrack->artists()->attach($artist);

        $otherTrack = Track::create([
            'title' => 'Another Song',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('tracks.index', [
                'query' => 'Searchable',
            ]));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('tracks/IndexPage')
                ->where('filters.query', 'Searchable')
                ->has('tracks.data', 1)
                ->where('tracks.data.0.id', $matchingTrack->id)
                ->where('tracks.data.0.title', 'Searchable Song')
        );

        $this->assertDatabaseHas('tracks', [
            'id' => $otherTrack->id,
            'title' => 'Another Song',
        ]);
    }
}
