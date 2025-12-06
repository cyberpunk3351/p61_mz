<?php

declare(strict_types=1);

namespace App\Http\Resources\Artist;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string|null $spotify_id
 * @property int|null $tracks_count
 * @property int|null $albums_count
 */
class ArtistsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'spotify_id' => $this->spotify_id,
            'tracks_count' => $this->tracks_count ?? 0,
            'albums_count' => $this->albums_count ?? 0,
        ];
    }
}
