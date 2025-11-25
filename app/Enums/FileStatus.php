<?php

declare(strict_types=1);

namespace App\Enums;

enum FileStatus: string
{
    case EXISTS = 'exists';
    case DOES_NOT_EXISTS = 'does not exist';
    case ERROR = 'error';
    case CREATED = 'created';

    public function description(): string
    {
        return match ($this) {
            self::EXISTS => 'File exists',
            self::DOES_NOT_EXISTS => 'File does not exist',
            self::ERROR => 'Error processing file',
            self::CREATED => 'Created',
        };
    }

    public function type(): int
    {
        return match ($this) {
            self::EXISTS => 1,
            self::DOES_NOT_EXISTS => 2,
            self::ERROR => 3,
            self::CREATED => 4,
        };
    }
}
