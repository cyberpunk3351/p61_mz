<?php

use App\Http\Controllers\Genre\ShowController;
use App\Http\Controllers\Genre\IndexController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/genres', IndexController::class)->name('genres.index');
    Route::get('/genres/{genre:slug}', ShowController::class)->name('genres.show');
});
