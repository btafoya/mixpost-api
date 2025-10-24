<?php

namespace Inovector\MixpostApi\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inovector\MixpostApi\Providers\MixpostApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->loadLaravelMigrations();
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
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
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set Mixpost configuration
        $app['config']->set('mixpost.user_model', \Illuminate\Foundation\Auth\User::class);

        // Set API configuration
        $app['config']->set('mixpost-api.prefix', 'api/mixpost');
        $app['config']->set('mixpost-api.rate_limit.enabled', false); // Disable rate limiting for tests
        $app['config']->set('mixpost-api.security.https_only', false); // Disable HTTPS requirement for tests
    }

    /**
     * Create a test user.
     */
    protected function createUser(array $attributes = []): \Illuminate\Foundation\Auth\User
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
    protected function createToken(\Illuminate\Foundation\Auth\User $user, string $name = 'test-token', array $abilities = ['*']): string
    {
        return $user->createToken($name, $abilities)->plainTextToken;
    }
}
