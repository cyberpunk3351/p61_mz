<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'spotify_id',
    ];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'artist_track', 'artist_id', 'track_id');
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'artist_album', 'artist_id', 'album_id');
    }
}
