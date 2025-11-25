<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $type
 * @property string $hash
 */
class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'hash',
    ];
    /**
     * Get the playlist associated with the file.
     */
    public function playlist(): HasOne
    {
        return $this->hasOne(Playlist::class);
    }

}
