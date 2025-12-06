<?php

use App\Http\Controllers\Artist\GetController;
use App\Http\Controllers\Artist\ShowController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'artists',
    'as' => 'artists.',
], function () {
    Route::get('/', GetController::class)->name('get');
    Route::get('/{artist}', ShowController::class)->name('show');
});
