<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $title
 * @property Carbon|null $release_date
 * @property int|null $rating
 * @property string|null $isrc
 * @property string|null $spotify_id
 * @property-read Collection<int, Artist> $artists
 * @property-read Collection<int, Album> $albums
 */

class Track extends Model
{
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'release_date',
        'rating',
        'spotify_track_id',
        'isrc',
        'spotify_id',
        'genre_id',
        'parent_genre_id',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    protected function releaseDate(): Attribute
    {
        return Attribute::make(
            get: static function (mixed $value): ?string {
                if ($value === null) {
                    return null;
                }

                return Carbon::parse($value)->format('d.m.y');
            },
        );
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_track', 'track_id', 'artist_id');
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'album_track', 'track_id', 'album_id');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'genre_track', 'track_id', 'genre_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['artists']);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'rating' => $this->rating,
            'release_date' => $this->getRawOriginal('release_date'),
            'artists' => $this->artists->pluck('name')->all(),
        ];
    }
}
