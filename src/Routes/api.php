<?php

use Btafoya\MixpostApi\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

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
    Route::middleware(['auth:sanctum', 'mixpost.token', 'throttle:api'])->group(function () {
        // Health check endpoint
        Route::get('health', function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0.0',
            ]);
        })->name('health');

        // Posts API
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'index'])->name('index');
            Route::post('/', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'store'])->name('store');
            Route::delete('/', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'show'])->name('show');
            Route::put('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'update'])->name('update');
            Route::delete('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'destroy'])->name('destroy');
            Route::post('{uuid}/schedule', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'schedule'])->name('schedule');
            Route::post('{uuid}/publish', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'publish'])->name('publish');
            Route::post('{uuid}/duplicate', [\Btafoya\MixpostApi\Http\Controllers\Api\PostsController::class, 'duplicate'])->name('duplicate');
        });

        // Media API
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'index'])->name('index');
            Route::post('/', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'store'])->name('store');
            Route::post('/download', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'download'])->name('download');
            Route::delete('/', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'show'])->name('show');
            Route::delete('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\MediaController::class, 'destroy'])->name('destroy');
        });

        // Accounts API
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [\Btafoya\MixpostApi\Http\Controllers\Api\AccountsController::class, 'index'])->name('index');
            Route::get('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\AccountsController::class, 'show'])->name('show');
            Route::put('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\AccountsController::class, 'update'])->name('update');
            Route::delete('{uuid}', [\Btafoya\MixpostApi\Http\Controllers\Api\AccountsController::class, 'destroy'])->name('destroy');
        });

        // Tags API
        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/', [\Btafoya\MixpostApi\Http\Controllers\Api\TagsController::class, 'index'])->name('index');
            Route::post('/', [\Btafoya\MixpostApi\Http\Controllers\Api\TagsController::class, 'store'])->name('store');
            Route::put('{id}', [\Btafoya\MixpostApi\Http\Controllers\Api\TagsController::class, 'update'])->name('update');
            Route::delete('{id}', [\Btafoya\MixpostApi\Http\Controllers\Api\TagsController::class, 'destroy'])->name('destroy');
        });

        // Phase 3 routes (Reports, Calendar, System) will be added later
    });

});
