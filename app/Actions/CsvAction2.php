<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class CsvAction2
{
    public function __construct(
        private StoreArtistAction $storeArtistFromRow,
    ) {}

    /**
     * Импорт артистов из загруженного CSV-файла.
     *
     * @return array{
     *     imported: int,
     *     skipped: int,
     *     total_rows: int
     * }
     */
    public function __invoke(UploadedFile $uploadedFile): array
    {
        $disk = Storage::disk('public');

        // Хэш файла, чтобы не грузить целиком в память
        $hash = hash_file('sha256', $uploadedFile->getRealPath());

        $extension    = $uploadedFile->getClientOriginalExtension();
        $filename     = $hash . ($extension ? '.' . $extension : '');
        $relativePath = 'uploads/' . $filename;

        if (! $disk->exists($relativePath)) {
            $uploadedFile->storeAs('uploads', $filename, 'public');
        }

        $file = File::updateOrCreate(
            ['hash' => $hash],
            [
                'name' => $uploadedFile->getClientOriginalName(),
                'type' => $uploadedFile->getMimeType(),
                'path' => $relativePath,
            ],
        );

        $absolutePath = $disk->path($file->path);

        return $this->importFromPath($absolutePath);
    }

    /**
     * Импорт артистов из CSV по пути к файлу.
     */
    private function importFromPath(string $absolutePath): array
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

            // Первая строка — заголовок
            if ($headerKeys === []) {
                [$headerKeys, $headerLabels] = $this->prepareHeader($data);

                continue;
            }

            if ($this->rowIsEmpty($data)) {
                continue;
            }

            $totalRows++;

            $row = $this->mapRow($headerKeys, $data);

            // Вынесенное сохранение артиста
            if (($this->storeArtistFromRow)($row)) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        fclose($handle);

        return [
            'imported'   => $imported,
            'skipped'    => $skipped,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Готовим заголовок: режем BOM, делаем snake_case, обеспечиваем уникальность ключей.
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

            // Срезаем BOM у первой колонки: "\u{FEFF}Track URI"
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

    /**
     * Пустая ли строка (нет полезных данных).
     */
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
     * Сопоставляем значения строки с ключами заголовка.
     *
     * @return array<string, string|null>
     */
    private function mapRow(array $headerKeys, array $row): array
    {
        $row = array_pad($row, count($headerKeys), null);

        return array_combine($headerKeys, $row) ?: [];
    }

    /**
     * Определяем разделитель по первой строке.
     */
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
