<?php

use Btafoya\MixpostApi\Http\Controllers\Api\AccountsController;
use Btafoya\MixpostApi\Http\Controllers\Api\MediaController;
use Btafoya\MixpostApi\Http\Controllers\Api\PostsController;
use Btafoya\MixpostApi\Http\Controllers\Api\TagsController;
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
            Route::delete('tokens/current', [TokenController::class, 'destroyCurrent'])->name('tokens.current.destroy');
            Route::delete('tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');
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

        // Posts API
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [PostsController::class, 'index'])->name('index');
            Route::post('/', [PostsController::class, 'store'])->name('store');
            Route::delete('/', [PostsController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/{id}', [PostsController::class, 'show'])->name('show');
            Route::put('/{id}', [PostsController::class, 'update'])->name('update');
            Route::delete('/{id}', [PostsController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/schedule', [PostsController::class, 'schedule'])->name('schedule');
            Route::post('/{id}/publish', [PostsController::class, 'publish'])->name('publish');
            Route::post('/{id}/duplicate', [PostsController::class, 'duplicate'])->name('duplicate');
        });

        // Media API
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/', [MediaController::class, 'store'])->name('store');
            Route::post('/download', [MediaController::class, 'download'])->name('download');
            Route::delete('/', [MediaController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/{id}', [MediaController::class, 'show'])->name('show');
            Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
        });

        // Accounts API
        Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/{id}', [AccountsController::class, 'show'])->name('accounts.show');
        Route::put('/accounts/{id}', [AccountsController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{id}', [AccountsController::class, 'destroy'])->name('accounts.destroy');

        // Tags API
        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/', [TagsController::class, 'index'])->name('index');
            Route::post('/', [TagsController::class, 'store'])->name('store');
            Route::put('/{id}', [TagsController::class, 'update'])->name('update');
            Route::delete('/{id}', [TagsController::class, 'destroy'])->name('destroy');
        });

        // Phase 3 routes (Reports, Calendar, System) will be added later
    });

});
