<?php

use Illuminate\Support\Facades\Route;
use Inovector\MixpostApi\Http\Controllers\Api\TokenController;

/*
|--------------------------------------------------------------------------
| Mixpost API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the MixpostApiServiceProvider and are
| automatically prefixed with the configured API prefix (default: api/mixpost)
|
*/

// Apply HTTPS middleware to all routes
Route::middleware(['mixpost.https'])->group(function () {

    // Authentication Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('tokens', [TokenController::class, 'create'])->name('tokens.create');

        Route::middleware(['auth:sanctum', 'mixpost.token'])->group(function () {
            Route::get('tokens', [TokenController::class, 'index'])->name('tokens.index');
            Route::delete('tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');
            Route::delete('tokens/current', [TokenController::class, 'destroyCurrent'])->name('tokens.current.destroy');
        });
    });

    // Protected API Routes
    Route::middleware(['auth:sanctum', 'mixpost.token'])->group(function () {
        // Health check endpoint
        Route::get('health', function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0.0',
            ]);
        })->name('health');

        // Resource routes will be added in Phase 2
        // Posts, Media, Accounts, Tags, Reports, Calendar, System
    });

});
