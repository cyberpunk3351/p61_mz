    <?php

    use App\Http\Controllers\File\{
        AddController,
        StoreController
    };
    use Illuminate\Support\Facades\Route;

    /**
     * Routes for file upload and management
     *
     * All routes require authentication and email verification.
     * Rate limiting is applied to prevent abuse.
     */
    Route::middleware(['auth', 'verified', 'throttle:10,1'])->prefix('files')->name('files.')->group(function () {
        /**
         * Show file upload form
         * GET /files/add
         * Name: files.add
         */
        Route::get('/add', AddController::class)->name('add');

        /**
         * Handle file upload submission
         * POST /files/store
         * Name: files.store
         */
        Route::post('/store', StoreController::class)->name('store');
    });
