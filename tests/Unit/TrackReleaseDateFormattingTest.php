<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackReleaseDateFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_date_is_formatted_as_day_month_year(): void
    {
        $track = Track::create([
            'title' => 'Future Track',
            'release_date' => '2025-12-03',
        ]);

        $this->assertSame('03.12.25', $track->release_date);
    }

    public function test_release_date_is_null_when_not_set(): void
    {
        $track = Track::create([
            'title' => 'Unknown Date',
        ]);

        $this->assertNull($track->release_date);
    }
}
