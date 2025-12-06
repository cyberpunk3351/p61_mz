<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Playlist;
use Illuminate\Support\Str;

readonly class CsvAction
{
    public function __construct(
        private StoreArtistAction $storeArtistFromRow,
        private StoreTrackAction $storeTrackFromRow,
        private StoreAlbumAction $storeAlbum,
        private SyncPlaylistTracksAction $syncPlaylistTracks,
        private SyncArtistsTracksAction $syncArtistsTracks,
        private SyncAlbumTracksAction $syncAlbumTracks,
        private SyncArtistsAlbumsAction $syncArtistsAlbums,
    ) {}

    public function __invoke(array $data, Playlist $playlist): array
    {
        return $this->importFromPath($data['path'], $playlist);
    }

    private function importFromPath(string $absolutePath, Playlist $playlist): array
    {
        if (! file_exists($absolutePath)) {
            return [
                'imported'   => 0,
                'skipped'    => 0,
                'total_rows' => 0,
            ];
        }

        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            return [
                'imported'   => 0,
                'skipped'    => 0,
                'total_rows' => 0,
            ];
        }

        $sample    = fgets($handle) ?: '';
        $delimiter = $this->detectDelimiter($sample);

        rewind($handle);

        $headerKeys   = [];
        $headerLabels = [];
        $totalRows    = 0;
        $imported     = 0;
        $skipped      = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data = array_map(
                static fn ($value) => is_string($value) ? trim($value) : $value,
                $data
            );

            if ($headerKeys === []) {
                [$headerKeys, $headerLabels] = $this->prepareHeader($data);

                continue;
            }

            if ($this->rowIsEmpty($data)) {
                continue;
            }

            $totalRows++;

            $row = $this->mapRow($headerKeys, $data);

            $artists = ($this->storeArtistFromRow)($row);
            $track = ($this->storeTrackFromRow)($row);
            $album = ($this->storeAlbum)($row);

            if ($track === null) {
                $skipped++;
                continue;
            }

            $artistModels = $artists['artists'] ?? [];

            ($this->syncPlaylistTracks)($playlist, $track);
            if ($artistModels !== []) {
                ($this->syncArtistsTracks)($artistModels, $track);
            }

            if ($album !== null) {
                ($this->syncAlbumTracks)($album, $track);
                if ($artistModels !== []) {
                    ($this->syncArtistsAlbums)($artistModels, $album);
                }
            }

            $imported++;
        }

        fclose($handle);

        return [
            'imported'   => $imported,
            'skipped'    => $skipped,
            'total_rows' => $totalRows,
        ];
    }

    /**
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function prepareHeader(array $headerRow): array
    {
        $keys        = [];
        $labels      = [];
        $occurrences = [];

        foreach ($headerRow as $index => $column) {
            $label = $column !== null && trim($column) !== ''
                ? trim($column)
                : 'Column ' . ($index + 1);

            $label = ltrim($label, "\xEF\xBB\xBF");

            $baseKey = Str::snake($label);
            $key     = $baseKey !== '' ? $baseKey : 'column_' . ($index + 1);

            if (isset($occurrences[$key])) {
                $occurrences[$key]++;
                $key = $key . '_' . $occurrences[$key];
            } else {
                $occurrences[$key] = 1;
            }

            $keys[]   = $key;
            $labels[] = $label;
        }

        return [$keys, $labels];
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)->every(function ($value) {
            if (is_string($value)) {
                return trim($value) === '';
            }

            return $value === null;
        });
    }

    /**
     * @return array<string, string|null>
     */
    private function mapRow(array $headerKeys, array $row): array
    {
        $row = array_pad($row, count($headerKeys), null);

        return array_combine($headerKeys, $row) ?: [];
    }

    private function detectDelimiter(string $sample): string
    {
        $delimiters = [',', ';', "\t", '|'];

        $counts = collect($delimiters)->mapWithKeys(
            static function (string $delimiter) use ($sample): array {
                return [$delimiter => substr_count($sample, $delimiter)];
            }
        );

        $bestDelimiter = $counts->sortDesc()->keys()->first();

        if ($bestDelimiter !== null && $counts->get($bestDelimiter) > 0) {
            return $bestDelimiter;
        }

        return ',';
    }
}
