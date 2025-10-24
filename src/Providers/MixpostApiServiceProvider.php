<?php

namespace Btafoya\MixpostApi\Providers;

use Btafoya\MixpostApi\Http\Middleware\EnforceHttps;
use Btafoya\MixpostApi\Http\Middleware\ValidateApiToken;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MixpostApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mixpost-api.php',
            'mixpost-api'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMiddleware();
        $this->registerRoutes();
    }

    /**
     * Register the package's middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('mixpost.https', EnforceHttps::class);
        $router->aliasMiddleware('mixpost.token', ValidateApiToken::class);
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/mixpost-api.php' => config_path('mixpost-api.php'),
            ], 'mixpost-api-config');
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        });
    }

    /**
     * Get the route configuration array.
     */
    protected function routeConfiguration(): array
    {
        $middleware = ['api'];

        // Add rate limiting if enabled
        if (config('mixpost-api.rate_limit.enabled', true)) {
            $maxAttempts = config('mixpost-api.rate_limit.max_attempts', 60);
            $decayMinutes = config('mixpost-api.rate_limit.decay_minutes', 1);
            $middleware[] = "throttle:{$maxAttempts},{$decayMinutes}";
        }

        return [
            'prefix' => config('mixpost-api.prefix', 'api/mixpost'),
            'middleware' => $middleware,
        ];
    }
}
