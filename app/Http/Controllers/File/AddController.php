<?php

declare(strict_types=1);

namespace App\Http\Controllers\File;

use Inertia\Inertia;
use Inertia\Response;

class AddController
{
    public function __invoke(): Response
    {
        return Inertia::render('file/Add', ['status' => '1111']);
    }
}
