<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArtistPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_artists_index_lists_artists_with_counts(): void
    {
        $user = User::factory()->create();

        $artistWithMusic = Artist::create([
            'name' => 'Older Artist',
            'spotify_id' => 'spotify-artist-1',
        ]);

        $artistWithMusic->forceFill([
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ])->save();

        $album = Album::create([
            'title' => 'First Album',
        ]);

        $track = Track::create([
            'title' => 'First Track',
        ]);

        $artistWithMusic->albums()->attach($album);
        $artistWithMusic->tracks()->attach($track);

        for ($index = 1; $index <= 9; $index++) {
            Artist::create([
                'name' => 'Filler '.$index,
            ]);
        }

        $latestArtist = Artist::create([
            'name' => 'Newest Artist',
            'spotify_id' => 'spotify-artist-2',
        ]);

        $latestArtist->albums()->attach($album);
        $latestArtist->tracks()->attach($track);

        $response = $this->actingAs($user)->get(route('artists.get'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('artists/IndexPage')
                ->has('artists.data', 10)
                ->where('artists.data.0.name', 'Filler 1')
                ->where(
                    'artists.data',
                    static fn ($artists): bool => collect($artists)
                        ->contains('id', $latestArtist->id)
                )
                ->where('artists.current_page', 1)
                ->where('artists.next_page_url', static fn (?string $value): bool => $value !== null)
                ->where(
                    'artists.data',
                    static function ($artists) use ($latestArtist): bool {
                        $artist = collect($artists)->firstWhere('id', $latestArtist->id);

                        return ($artist['tracks_count'] ?? 0) === 1
                            && ($artist['albums_count'] ?? 0) === 1;
                    }
                )
        );
    }

    public function test_artist_show_displays_details_and_tracks(): void
    {
        $user = User::factory()->create();

        $artist = Artist::create([
            'name' => 'Featured Artist',
            'spotify_id' => 'spotify-featured',
        ]);

        $album = Album::create([
            'title' => 'Highlights',
        ]);

        $artist->albums()->attach($album);

        for ($index = 1; $index <= 16; $index++) {
            $track = Track::create([
                'title' => 'Track '.$index,
            ]);

            $artist->tracks()->attach($track);
            $album->tracks()->attach($track);
        }

        $response = $this->actingAs($user)->get(route('artists.show', $artist));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('artists/ShowPage')
                ->where('artist.data.id', $artist->id)
                ->where('artist.data.tracks_count', 16)
                ->where('artist.data.albums.data.0.title', $album->title)
                ->has('tracks.data', 15)
                ->where('tracks.next_page_url', static fn (?string $value): bool => $value !== null)
                ->where('tracks.data.0.artist', [$artist->name])
        );
    }
}
