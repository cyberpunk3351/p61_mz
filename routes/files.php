<?php

use App\Http\Controllers\File\{
    AddController,
    StoreController
};
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'files',
    'as'     => 'files.',
], function () {
    Route::get('/', AddController::class)->name('add');
    Route::post('/', StoreController::class)->name('store');
});
