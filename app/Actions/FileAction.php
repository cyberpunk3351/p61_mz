<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FileStatus;
use App\Models\Playlist;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Illuminate\Support\Facades\DB;

readonly class FileAction
{
    private const ALLOWED_MIME_TYPES = [
        'text/plain',
        'application/json',
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const TEMP_DIRECTORY = 'uploads/tmp/';
    private const FILES_DIRECTORY = 'uploads/';
    private const DEFAULT_DISK = 'public';

    public function __invoke(UploadedFile $uploadedFile, ?string $type = null): array
    {
        self::validateFile($uploadedFile);
        $hash = self::generateHash($uploadedFile);

//        if (self::fileExists($hash, self::DEFAULT_DISK)) {
//            $playlist = PlaylistAction::findByHash($hash);
//            $playlist?->load('file');
//            return [
//                'status' => FileStatus::EXISTS->description(),
//                'status_id' => FileStatus::EXISTS->type(),
//                'playlist' => $playlist
//            ];
//        }

        return DB::transaction(function () use ($uploadedFile, $hash, $type) {
            $path = self::storeFile($uploadedFile, $hash);
            return [
                'status' => FileStatus::CREATED->description(),
                'status_id' => FileStatus::CREATED->type(),
                'path' => $path,
                'hash' => $hash
            ];
        });

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

    /**
     * Generate a hash file
     *
     * @param UploadedFile $file
     * @return string
     */
    public static function generateHash(UploadedFile $file): string
    {
        return md5_file($file->getPathname());
    }

    /**
     * Check if a file exists in the files directory.
     *
     * @param string $filename The filename to check
     * @param string $disk The storage disk
     * @return bool True if file exists
     */
    public static function fileExists(string $filename, string $disk = self::DEFAULT_DISK): bool
    {
        return Storage::disk($disk)->exists(self::FILES_DIRECTORY . $filename . '.csv');
    }

    /**
     * Store the uploaded file
     *
     * @param UploadedFile $uploadedFile
     * @param string $hash
     * @return string
     */
    private static function storeFile(UploadedFile $uploadedFile, string $hash): string
    {
        self::validateUploadedFile($uploadedFile);

        $tempFilename = $uploadedFile->getFilename() . '.' . 'csv';
        $finalFilename = $hash . '.' . 'csv';

        try {
            $tempPath = self::storeTemporarily($uploadedFile, $tempFilename, self::DEFAULT_DISK);
            $absolutePath = self::moveToFinalLocation($tempPath, $finalFilename, self::DEFAULT_DISK);
            return $absolutePath;
        } catch (\Exception $e) {
            // Clean up temporary file if it exists
            self::cleanupTempFile($tempFilename, self::DEFAULT_DISK);
            throw new RuntimeEcxception('Failed to store file: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate uploaded file.
     *
     * @param UploadedFile $uploadedFile
     * @throws InvalidArgumentException
     */
    private static function validateUploadedFile(UploadedFile $uploadedFile): void
    {
        if (!$uploadedFile->isValid()) {
            throw new InvalidArgumentException('Uploaded file is not valid');
        }

        if ($uploadedFile->getSize() === 0) {
            throw new InvalidArgumentException('Uploaded file is empty');
        }
    }

    /**
     * Store file temporarily in the temp directory.
     *
     * @param UploadedFile $uploadedFile
     * @param string $filename
     * @param string $disk
     * @return string The temporary file path
     * @throws RuntimeException
     */
    private static function storeTemporarily(
        UploadedFile $uploadedFile,
        string $filename,
        string $disk = self::DEFAULT_DISK
    ): string {
        try {
            $uploadedFile->storeAs(self::TEMP_DIRECTORY, $filename, $disk);
            return Storage::disk($disk)->path(self::TEMP_DIRECTORY . $filename);
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to store file temporarily: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Move file from temporary location to final location.
     *
     * @param string $tempPath The temporary file path
     * @param string $finalFilename The final filename
     * @param string $disk The storage disk
     * @return string The final file path
     * @throws RuntimeException
     */
    private static function moveToFinalLocation(
        string $tempPath,
        string $finalFilename,
        string $disk = self::DEFAULT_DISK
    ): string {
        $finalPath = Storage::disk($disk)->path(self::FILES_DIRECTORY . $finalFilename);

        try {
            if (!\File::move($tempPath, $finalPath)) {
                throw new RuntimeException('File move operation failed');
            }

            return $finalPath;
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to move file to final location: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clean up temporary file.
     *
     * @param string $filename The temporary filename
     * @param string $disk The storage disk
     * @return bool True if cleanup was successful or file didn't exist
     */
    private static function cleanupTempFile(string $filename, string $disk = self::DEFAULT_DISK): bool
    {
        return Storage::disk($disk)->delete(self::TEMP_DIRECTORY . $filename);
    }
}
