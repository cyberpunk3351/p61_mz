<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\FileAction;
use App\Models\File;
use App\Models\Playlist;
use App\Models\Track;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;

class PlaylistAction
{
    private const ALLOWED_MIME_TYPES = [
        'text/plain',
        'application/json',
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    /**
     * Build a query to find playlists by ID or name.
     *
     * @param string|null $hash The playlist name to search for
     * @return ?Playlist The playlist instance
     * @throws InvalidArgumentException When both parameters are null
     * @throws Exception
     */
    public static function query(?string $hash): ?Playlist
    {
        self::validateParameters($hash);

        $playlist = Playlist::query()
            ->with('file')
            ->whereHas('file', function (Builder $query) use ($hash): void {
                $query->where('hash', $hash);
            })
            ->first();
        return $playlist;
    }

    /**
     * Find a single playlist by hash.
     *
     * @param string|null $hash The playlist hash to search for
     * @return Playlist|null The found model instance or null if not found
     * @throws InvalidArgumentException When both parameters are null
     */
    public static function findByHash(?string $hash): ?Playlist
    {
        return self::query($hash);
    }

    /**
     * Find a playlist by file hash, or create one if it doesn't exist.
     *
     * @param string $hash The file hash to search for
     * @return Playlist The found or created playlist
     * @throws InvalidArgumentException When hash is empty or file doesn't exist
     */
    public static function findOrCreate(string $hash, array $playlistData = []): Playlist
    {
        self::validateParameters($hash);

        // First, try to find existing playlist
        $playlist = self::findByHash($hash);

        if ($playlist !== null) {
            return $playlist;
        }

        // If not found, create a new one
        return self::createPlaylist($hash);
    }

    /**
     * Create a new playlist for the given file hash.
     *
     * @param string $hash The file hash
     * @param array $playlistData Additional data for playlist creation
     * @return Playlist The created playlist
     * @throws InvalidArgumentException When file doesn't exist
     */
    private static function createPlaylist(string $hash): Playlist
    {
        // Find the file by hash
        $file = File::where('hash', $hash)->first();
        dd($file);

        if ($file === null) {
            throw new InvalidArgumentException("File with hash '{$hash}' does not exist");
        }

        // Prepare playlist data with defaults
        $defaultData = [
            'title' => self::generatePlaylistName($file),
            'description' => "Playlist generated from file: {$file->name}",
        ];

        return Playlist::create($defaultData);
    }

    /**
     * Generate a default playlist name based on file information.
     *
     * @param File $file The file model
     * @return string Generated playlist name
     */
    private static function generatePlaylistName(File $file): string
    {
        $baseName = pathinfo($file->name ?? 'playlist', PATHINFO_FILENAME);

        // Clean up the name and make it more readable
        $name = str_replace(['_', '-'], ' ', $baseName);
        $name = ucwords(strtolower($name));

        // Add timestamp to make it unique if needed
        $timestamp = now()->format('Y-m-d H:i');

        return "{$name} - {$timestamp}";
    }

    public static function findOrNew(UploadedFile $file, ?string $type): bool | Playlist
    {
        self::validateFile($file);

        $hash = FileAction::generateHash($file);

        $file = FileAction::execute($file, $type);
//        if($file) {
//            return true;
//        }

        $playlist = self::findOrCreate($hash);

        return $playlist;


//        try {
//            $csv = Reader::createFromPath($csv, 'r');
//            $csv->setDelimiter(',');
//            $count_records = $csv->count();
//        } catch (Exception $e) {
//            // Handle exceptions (e.g., log the error)
//            Log::error('Error processing file: '.$e->getMessage());
//            // Optionally, rethrow the exception or handle it as needed
//            throw $e;
//        }

        $hasPlaylist = Playlist::with('file')->where('title', $title)->first();
        $hasPlaylistFile = $hasPlaylist->file ?? null;

        //
        //        return [
        //            'csv' => $csv,
        //            'count_records' => $count_records
        //        ];
    }

    /**
     * Validate that at least one parameter is provided.
     *
     * @param string|null $hash
     * @throws InvalidArgumentException
     */
    private static function validateParameters(?string $hash): void
    {
        if ($hash !== null && trim($hash) === '') {
            throw new InvalidArgumentException('Hash cannot be empty');
        }
    }

    /**
     * Validate the uploaded file
     *
     * @param UploadedFile $file
     * @throws InvalidArgumentException
     */
    private static function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('The uploaded file is not valid');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException(
                'File size exceeds maximum allowed size of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB'
            );
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException(
                'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_MIME_TYPES)
            );
        }
    }

    public static function syncPlaylistTracks(Playlist $playlist, Track $track): bool
    {
        try {
            $playlist->tracks()->save($track);
            dump(true);
            return true;
        } catch (Exception $e) {
            dump(false);
            // Log error if needed
            return false;
        }
    }

}
