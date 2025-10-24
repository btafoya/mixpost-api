# Mixpost REST API Add-on - Implementation Plan

**Version**: 1.0
**Date**: 2025-10-23
**Type**: Laravel Package Extension for Mixpost

---

## 🎯 Project Overview

**Goal**: Create a Laravel package that extends Mixpost with REST API capabilities for n8n integration.

**Approach**: Separate package that hooks into existing Mixpost infrastructure without modifying core files.

**Package Structure**:
```
mixpost-api-addon/
├── src/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   ├── Providers/
│   │   └── MixpostApiServiceProvider.php
│   ├── Routes/
│   │   └── api.php
│   └── helpers.php
├── database/
│   └── migrations/
├── config/
│   └── mixpost-api.php
├── tests/
│   └── Feature/
├── composer.json
└── README.md
```

---

## 📋 Phase 1: Foundation Setup

### 1.1 Package Initialization

**Create Package Directory**:
```bash
mkdir -p src/Http/Controllers/Api
mkdir -p src/Http/Middleware
mkdir -p src/Http/Requests
mkdir -p src/Http/Resources
mkdir -p src/Providers
mkdir -p src/Routes
mkdir -p database/migrations
mkdir -p config
mkdir -p tests/Feature
```

**Composer Configuration** (`composer.json`):
```json
{
  "name": "inovector/mixpost-api",
  "description": "REST API add-on for Mixpost - enables n8n and external integrations",
  "type": "library",
  "license": "MIT",
  "authors": [
    {
      "name": "Your Name",
      "email": "your@email.com"
    }
  ],
  "require": {
    "php": "^8.2",
    "illuminate/contracts": "^10.47|^11.0|^12.0",
    "laravel/sanctum": "^3.0|^4.0",
    "inovector/mixpost": "^1.0"
  },
  "require-dev": {
    "orchestra/testbench": "^9.0|^10.0",
    "pestphp/pest": "^2.34|^3.0",
    "pestphp/pest-plugin-laravel": "^2.0|^3.0"
  },
  "autoload": {
    "psr-4": {
      "Inovector\\MixpostApi\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Inovector\\MixpostApi\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Inovector\\MixpostApi\\Providers\\MixpostApiServiceProvider"
      ]
    }
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

### 1.2 Service Provider

**Create** `src/Providers/MixpostApiServiceProvider.php`:
```php
<?php

namespace Inovector\MixpostApi\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MixpostApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mixpost-api.php', 'mixpost-api'
        );
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/mixpost-api.php' => config_path('mixpost-api.php'),
        ], 'mixpost-api-config');

        // Register middleware
        $this->registerMiddleware();
    }

    protected function registerMiddleware()
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('mixpost.api', \Inovector\MixpostApi\Http\Middleware\ValidateMixpostApi::class);
    }
}
```

### 1.3 Configuration File

**Create** `config/mixpost-api.php`:
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    */
    'prefix' => 'api/mixpost',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    */
    'token' => [
        'expiration' => null, // null = never expire
        'abilities_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'security' => [
        'ip_whitelist_enabled' => false,
        'ip_whitelist' => [],
        'https_only' => env('APP_ENV') === 'production',
    ],
];
```

---

## 📋 Phase 2: Authentication Implementation

### 2.1 Install Sanctum

**Migration** (if not exists):
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2.2 API Token Controller

**Create** `src/Http/Controllers/Api/ApiTokenController.php`:
```php
<?php

namespace Inovector\MixpostApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiTokenController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token_name' => 'required|string|max:255',
            'abilities' => 'array',
        ]);

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken(
            $request->token_name,
            $request->abilities ?? ['*']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => config('mixpost-api.token.expiration'),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get();

        return response()->json([
            'data' => $tokens->map(fn($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
            ]),
        ]);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (!$token) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        $token->delete();

        return response()->json(['message' => 'Token revoked successfully']);
    }
}
```

### 2.3 Middleware

**Create** `src/Http/Middleware/ValidateMixpostApi.php`:
```php
<?php

namespace Inovector\MixpostApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateMixpostApi
{
    public function handle(Request $request, Closure $next)
    {
        // IP Whitelist check
        if (config('mixpost-api.security.ip_whitelist_enabled')) {
            $allowedIps = config('mixpost-api.security.ip_whitelist', []);

            if (!in_array($request->ip(), $allowedIps)) {
                return response()->json([
                    'message' => 'Unauthorized IP address'
                ], 403);
            }
        }

        // HTTPS check in production
        if (config('mixpost-api.security.https_only') && !$request->secure()) {
            return response()->json([
                'message' => 'HTTPS required'
            ], 400);
        }

        return $next($request);
    }
}
```

---

## 📋 Phase 3: API Resources

### 3.1 Post Resource

**Create** `src/Http/Resources/PostResource.php`:
```php
<?php

namespace Inovector\MixpostApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status->value,
            'schedule_status' => $this->schedule_status->value,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'versions' => PostVersionResource::collection($this->whenLoaded('versions')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### 3.2 Additional Resources

**Create similar resources for**:
- `AccountResource.php`
- `MediaResource.php`
- `TagResource.php`
- `PostVersionResource.php`

---

## 📋 Phase 4: API Controllers

### 4.1 Posts Controller

**Create** `src/Http/Controllers/Api/PostsController.php`:
```php
<?php

namespace Inovector\MixpostApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Http\Requests\StorePost;
use Inovector\Mixpost\Http\Requests\UpdatePost;
use Inovector\MixpostApi\Http\Resources\PostResource;
use Inovector\Mixpost\Builders\PostQuery;

class PostsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            $request->input('per_page', config('mixpost-api.pagination.default_per_page')),
            config('mixpost-api.pagination.max_per_page')
        );

        $posts = PostQuery::apply($request)
            ->latest()
            ->latest('id')
            ->paginate($perPage);

        return PostResource::collection($posts)->response();
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['accounts', 'versions', 'tags']);

        return (new PostResource($post))->response();
    }

    public function store(StorePost $request): JsonResponse
    {
        $post = $request->handle();
        $post->load(['accounts', 'versions', 'tags']);

        return (new PostResource($post))
            ->additional(['message' => 'Post created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePost $request, Post $post): JsonResponse
    {
        $post = $request->handle($post);
        $post->load(['accounts', 'versions', 'tags']);

        return (new PostResource($post))
            ->additional(['message' => 'Post updated successfully'])
            ->response();
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }

    public function schedule(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
        ]);

        $scheduledAt = "{$request->date} {$request->time}";
        $post->setScheduled(\Inovector\Mixpost\Util::convertTimeToUTC($scheduledAt));

        return (new PostResource($post))
            ->additional(['message' => 'Post scheduled successfully'])
            ->response();
    }

    public function publish(Post $post): JsonResponse
    {
        $post->setScheduled(now()->addSeconds(30));

        return (new PostResource($post))
            ->additional(['message' => 'Post queued for immediate publishing'])
            ->response();
    }

    public function duplicate(Post $post): JsonResponse
    {
        $newPost = Post::create([
            'status' => \Inovector\Mixpost\Enums\PostStatus::DRAFT,
            'scheduled_at' => null,
        ]);

        $newPost->accounts()->attach($post->accounts->pluck('id'));
        $newPost->tags()->attach($post->tags->pluck('id'));
        $newPost->versions()->createMany(
            $post->versions->map(fn($v) => $v->only(['account_id', 'is_original', 'content']))->toArray()
        );

        return (new PostResource($newPost))
            ->additional(['message' => 'Post duplicated successfully'])
            ->response()
            ->setStatusCode(201);
    }
}
```

### 4.2 Additional Controllers

**Create similar controllers for**:
- `MediaController.php`
- `AccountsController.php`
- `TagsController.php`
- `ReportsController.php`
- `CalendarController.php`
- `SystemStatusController.php`

---

## 📋 Phase 5: API Routes

**Create** `src/Routes/api.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use Inovector\MixpostApi\Http\Controllers\Api;

Route::prefix(config('mixpost-api.prefix'))->group(function () {
    // Public: Token generation
    Route::post('auth/tokens', [Api\ApiTokenController::class, 'create']);

    // Protected: Require authentication
    Route::middleware(['auth:sanctum', 'mixpost.api', 'throttle:api'])->group(function () {
        // Token management
        Route::get('auth/tokens', [Api\ApiTokenController::class, 'index']);
        Route::delete('auth/tokens/{tokenId}', [Api\ApiTokenController::class, 'destroy']);

        // Posts
        Route::apiResource('posts', Api\PostsController::class)
            ->parameters(['posts' => 'post:uuid']);
        Route::post('posts/{post:uuid}/schedule', [Api\PostsController::class, 'schedule']);
        Route::post('posts/{post:uuid}/publish', [Api\PostsController::class, 'publish']);
        Route::post('posts/{post:uuid}/duplicate', [Api\PostsController::class, 'duplicate']);

        // Media
        Route::apiResource('media', Api\MediaController::class)
            ->parameters(['media' => 'media:uuid']);
        Route::post('media/download', [Api\MediaController::class, 'download']);

        // Accounts
        Route::apiResource('accounts', Api\AccountsController::class)
            ->parameters(['accounts' => 'account:uuid'])
            ->except(['store']); // Don't allow creating accounts via API

        // Tags
        Route::apiResource('tags', Api\TagsController::class);

        // Reports
        Route::get('reports/dashboard', [Api\ReportsController::class, 'dashboard']);
        Route::get('reports/accounts/{account:uuid}', [Api\ReportsController::class, 'account']);
        Route::get('reports/posts/{post:uuid}', [Api\ReportsController::class, 'post']);

        // Calendar
        Route::get('calendar', [Api\CalendarController::class, 'index']);

        // System
        Route::get('system/status', [Api\SystemStatusController::class, 'index']);
    });
});
```

---

## 📋 Phase 6: Testing

### 6.1 Feature Tests

**Create** `tests/Feature/PostsApiTest.php`:
```php
<?php

namespace Inovector\MixpostApi\Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\Account;

class PostsApiTest extends TestCase
{
    public function test_can_list_posts()
    {
        Sanctum::actingAs($this->createUser(), ['*']);

        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_can_create_post()
    {
        Sanctum::actingAs($this->createUser(), ['*']);

        $account = Account::factory()->create();

        $response = $this->postJson('/api/mixpost/posts', [
            'accounts' => [$account->id],
            'versions' => [
                [
                    'is_original' => true,
                    'account_id' => null,
                    'content' => [
                        ['body' => 'Test post', 'media' => []]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data', 'message']);
    }

    public function test_cannot_access_without_authentication()
    {
        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(401);
    }

    protected function createUser()
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::factory()->create();
    }
}
```

---

## 📋 Phase 7: Installation & Documentation

### 7.1 Installation Instructions

**README.md**:
```markdown
# Mixpost REST API Add-on

REST API extension for Mixpost to enable n8n and external integrations.

## Installation

1. Install via Composer:
```bash
composer require inovector/mixpost-api
```

2. Publish configuration:
```bash
php artisan vendor:publish --tag=mixpost-api-config
```

3. Install Laravel Sanctum (if not already installed):
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

4. Add `HasApiTokens` trait to your User model:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

## Configuration

Edit `config/mixpost-api.php` to customize:
- API prefix
- Rate limiting
- Token expiration
- Security settings

## Usage

### Generate API Token

```bash
curl -X POST http://your-domain.com/api/mixpost/auth/tokens \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password",
    "token_name": "n8n-integration"
  }'
```

### Use Token

```bash
curl -X GET http://your-domain.com/api/mixpost/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## API Documentation

See [API_SPECIFICATION.md](./API_SPECIFICATION.md) for complete endpoint documentation.

## n8n Integration

See [N8N_EXAMPLES.md](./N8N_EXAMPLES.md) for workflow examples.
```

---

## 📋 Phase 8: Deployment Checklist

### 8.1 Pre-Deployment

- [ ] Run all tests: `php artisan test`
- [ ] Check code style: `vendor/bin/pint`
- [ ] Verify migrations work
- [ ] Test authentication flow
- [ ] Verify rate limiting works
- [ ] Test all CRUD operations
- [ ] Check error handling
- [ ] Verify API resources format correctly
- [ ] Test pagination

### 8.2 Deployment

- [ ] Tag release version
- [ ] Publish to Packagist (if public)
- [ ] Update documentation
- [ ] Create migration guide
- [ ] Test in staging environment
- [ ] Deploy to production
- [ ] Monitor error logs
- [ ] Test n8n integration

---

## 🎯 Implementation Timeline

### Week 1: Foundation
- Day 1-2: Package structure & service provider
- Day 3-4: Authentication implementation
- Day 5: Testing & validation

### Week 2: Core APIs
- Day 1-2: Posts API (CRUD + schedule/publish)
- Day 3: Media API
- Day 4: Accounts & Tags API
- Day 5: Testing

### Week 3: Advanced Features
- Day 1-2: Reports & Analytics API
- Day 3: Calendar & System APIs
- Day 4: Error handling & validation
- Day 5: Integration testing

### Week 4: Documentation & Deployment
- Day 1-2: API documentation
- Day 3: n8n examples & guides
- Day 4: Testing & bug fixes
- Day 5: Deployment & monitoring

---

## 📊 Success Metrics

- ✅ All endpoints functional
- ✅ 100% test coverage for critical paths
- ✅ Response time < 200ms for simple requests
- ✅ n8n workflows working end-to-end
- ✅ Zero security vulnerabilities
- ✅ Comprehensive documentation
- ✅ Easy installation process

---

**Document Version**: 1.0
**Last Updated**: 2025-10-23
