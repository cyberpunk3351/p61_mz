<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

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
