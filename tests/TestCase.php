<?php

namespace Btafoya\MixpostApi\Tests;

use Btafoya\MixpostApi\Providers\MixpostApiServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable mass assignment protection for tests
        \Illuminate\Database\Eloquent\Model::unguard();

        // Load Mixpost factories
        $this->withFactories(__DIR__.'/../vendor/inovector/mixpost/database/factories');

        // Run migrations
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../vendor/inovector/mixpost/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../vendor/laravel/sanctum/database/migrations');
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Laravel\Sanctum\SanctumServiceProvider::class,
            \Inovector\Mixpost\MixpostServiceProvider::class,
            MixpostApiServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set Mixpost configuration
        $app['config']->set('mixpost.user_model', \Btafoya\MixpostApi\Tests\Support\User::class);
        $app['config']->set('mixpost.disk', 'public');
        $app['config']->set('mixpost.max_file_size', 102400);

        // Set filesystem disks
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ]);

        // Set API configuration
        $app['config']->set('mixpost-api.prefix', 'api/mixpost');
        $app['config']->set('mixpost-api.rate_limit.enabled', false); // Disable rate limiting for tests
        $app['config']->set('mixpost-api.security.https_only', false); // Disable HTTPS requirement for tests

        // Set Sanctum configuration
        $app['config']->set('sanctum.stateful', []);
    }

    /**
     * Create a test user.
     */
    protected function createUser(array $attributes = []): \Btafoya\MixpostApi\Tests\Support\User
    {
        $userModel = config('mixpost.user_model');

        return $userModel::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }

    /**
     * Create an API token for a user.
     */
    protected function createToken(\Btafoya\MixpostApi\Tests\Support\User $user, string $name = 'test-token', array $abilities = ['*']): string
    {
        return $user->createToken($name, $abilities)->plainTextToken;
    }
}
