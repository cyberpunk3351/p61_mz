<?php

use App\Http\Controllers\File\{
    AddController,
    StoreController
};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('files')->name('files.')->group(function () {
    Route::get('/', AddController::class)->name('add');
    Route::post('/', StoreController::class)->name('store');
});
