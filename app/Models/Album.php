<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'year',
        'rating',
        'type',
        'spotify_id',
        'isrc',
        'img_640_url',
        'img_300_url',
        'img_64_url',
    ];

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_album', 'album_id', 'artist_id');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'album_track', 'album_id', 'track_id');
    }

    public function file(): HasOne
    {
        return $this->hasOne(File::class);
    }
}
