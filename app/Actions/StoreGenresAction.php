<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Genre;
use Illuminate\Support\Str;

class StoreGenresAction
{
    /**
     * @param array<string, mixed> $row
     * @return array{
     *     genre_id: int|null,
     *     parent_genre_id: int|null,
     *     all_genre_ids: array<int>
     * }
     */
    public function __invoke(array $row): array
    {
        $genreResult = $this->resolveGenreIds($row['genres'] ?? null, false);
        $parentGenreResult = $this->resolveGenreIds($row['parent_genres'] ?? null, true);

        $allIds = array_merge(
            $genreResult['all_ids'],
            $parentGenreResult['all_ids']
        );

        return [
            'genre_id' => $genreResult['primary_id'],
            'parent_genre_id' => $parentGenreResult['primary_id'],
            'all_genre_ids' => array_unique($allIds),
        ];
    }

    /**
     * @return array{primary_id: int|null, all_ids: array<int>}
     */
    private function resolveGenreIds(?string $genreString, bool $isParent): array
    {
        if ($genreString === null || trim($genreString) === '') {
            return ['primary_id' => null, 'all_ids' => []];
        }

        // Split by comma, trim whitespace
        $names = array_map('trim', explode(',', $genreString));
        
        $primaryId = null;
        $allIds = [];

        foreach ($names as $index => $name) {
            if ($name === '') {
                continue;
            }

            $genre = Genre::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'parent_genre' => $isParent,
                ]
            );

            $allIds[] = $genre->id;

            // If it's the first one, capture the ID
            if ($index === 0) {
                $primaryId = $genre->id;
            }
        }

        return [
            'primary_id' => $primaryId,
            'all_ids' => $allIds,
        ];
    }
}