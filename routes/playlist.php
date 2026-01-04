<?php

use App\Http\Controllers\Playlist\DetachTrackController;
use App\Http\Controllers\Playlist\GetController;
use App\Http\Controllers\Playlist\SearchController;
use App\Http\Controllers\Playlist\ShowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('playlists')->name('playlists.')->group(function () {
    Route::get('/', GetController::class)->name('get');
    Route::get('/{playlist}', ShowController::class)->name('show');
    Route::delete('/{playlist}/tracks/{track}', DetachTrackController::class)->name('tracks.detach');
    Route::get('/search/{playlist}/track', SearchController::class)->name('search');
});
