<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Artist;

class StoreArtistAction
{
    /**
     * @param  array<string, string|null>  $row
     * @return array{imported: int, skipped: int}
     */
    public function __invoke(array $row): array
    {

        $namesRaw  = $row['artist'] ?? null;

        if (! is_string($namesRaw) || trim($namesRaw) === '') {
            return [
                'imported' => 0,
                'skipped'  => 0,
            ];
        }

        $names = preg_split('/[;,]/', $namesRaw) ?: [];

        $imported = 0;
        $skipped  = 0;
        $artists = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $artist = Artist::firstOrCreate(
                [
                    'name' => $name,
                ]
            );

            $artists[] = $artist;

            if ($artist->wasRecentlyCreated) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        return [
            'imported' => $imported,
            'skipped'  => $skipped,
            'artists'  => $artists,
        ];
    }
}
