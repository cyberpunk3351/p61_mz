<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackRatingUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_set_track_rating(): void
    {
        $user = User::factory()->create();
        $track = Track::create([
            'title' => 'Rated Track',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('tracks.rating', $track), [
                'rating' => 5,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'rating' => 5,
        ]);
    }

    public function test_rating_validation_limits_values_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $track = Track::create([
            'title' => 'Unrated Track',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('tracks.rating', $track), [
                'rating' => 0,
            ]);

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'rating' => null,
        ]);
    }
}
