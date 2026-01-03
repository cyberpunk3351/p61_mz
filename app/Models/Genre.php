<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_genre',
    ];
    
    protected $casts = [
        'parent_genre' => 'boolean',
    ];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'genre_track', 'genre_id', 'track_id');
    }
}
