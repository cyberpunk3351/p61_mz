<?php

declare(strict_types=1);

namespace App\Http\Controllers\File;

use App\Actions\CsvAction;
use App\Actions\FileAction;
use App\Actions\StorePlaylistAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileRequest;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
{
    public function __construct(
        private readonly StorePlaylistAction $storePlaylist,
    ) {}

    public function __invoke(FileRequest $request, FileAction $fileAction, CsvAction $csvAction): RedirectResponse
    {
        /** @var \Illuminate\Http\UploadedFile|null $uploadedFile */
        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return back()->withErrors([
                'file' => 'File is required.',
            ]);
        }

        $fileAction = $fileAction($uploadedFile, 'playlist');
        $storePlaylist = ($this->storePlaylist)($uploadedFile->getClientOriginalName(), $fileAction['hash'], 'playlist');
        $csvAction($fileAction, $storePlaylist);


        return to_route('files.add')
            ->with('status', $fileAction['status'])
            ->with('status_id',  $fileAction['status_id'] ?? null);
    }
}
