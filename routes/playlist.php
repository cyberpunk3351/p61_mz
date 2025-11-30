<?php

use App\Http\Controllers\Playlist\{
    GetController,
};
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'playlists',
    'as'     => 'playlists.',
], function () {
    Route::get('/', GetController::class)->name('get');
});
