<?php

use App\Http\Controllers\Playlist\DetachTrackController;
use App\Http\Controllers\Playlist\GetController;
use App\Http\Controllers\Playlist\ShowController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'playlists',
    'as' => 'playlists.',
], function () {
    Route::get('/', GetController::class)->name('get');
    Route::get('/{playlist}', ShowController::class)->name('show');
    Route::delete('/{playlist}/tracks/{track}', DetachTrackController::class)->name('tracks.detach');
});
