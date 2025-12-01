<?php

use App\Http\Controllers\Playlist\{
    GetController,
    ShowController
};
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'playlists',
    'as'     => 'playlists.',
], function () {
    Route::get('/', GetController::class)->name('get');
    Route::get('/{playlist}', ShowController::class)->name('show');
});
