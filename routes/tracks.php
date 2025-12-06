<?php

use App\Http\Controllers\Track\UpdateRatingController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'tracks',
    'as' => 'tracks.',
], function () {
    Route::patch('/{track}/rating', UpdateRatingController::class)->name('rating');
});
