<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Playlist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'hash',
        'source',
        'description',
        'hash',
        'type',
    ];

    /**
     * Get the playlist's file.
     */
    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'morphable');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'playlist_track', 'playlist_id', 'track_id');
    }
}
