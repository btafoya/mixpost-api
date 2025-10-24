# Mixpost REST API - Authentication Strategy

**Purpose**: Laravel Sanctum token-based authentication for REST API add-on

---

## 🎯 Strategy Overview

**Chosen Approach**: Laravel Sanctum API Tokens

### Why Laravel Sanctum?
- ✅ **Native Laravel Integration**: Built-in support, no additional dependencies
- ✅ **Simple Token Management**: Easy to generate, revoke, and manage
- ✅ **Performance**: Lightweight, fast token validation
- ✅ **Security**: Secure token hashing, proper Laravel security practices
- ✅ **Flexibility**: Supports abilities/permissions per token
- ✅ **n8n Compatible**: Works perfectly with HTTP Request node authentication

---

## 🔧 Implementation Details

### 1. Installation & Configuration

**Install Sanctum** (if not already installed):
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configure Sanctum** (`config/sanctum.php`):
```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null, // Tokens never expire by default

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

**Add to User Model**:
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    // ... rest of model
}
```

---

### 2. Token Generation Endpoint

**Route** (`routes/api.php`):
```php
Route::prefix('mixpost')->group(function () {
    Route::post('auth/tokens', [ApiTokenController::class, 'create']);
});
```

**Controller** (`app/Http/Controllers/Api/ApiTokenController.php`):
```php
<?php

namespace Inovector\Mixpost\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ApiTokenController extends Controller
{
    /**
     * Generate new API token
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token_name' => 'required|string|max:255',
            'abilities' => 'array',
            'expires_at' => 'nullable|date'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate token with optional abilities
        $token = $user->createToken(
            $request->token_name,
            $request->abilities ?? ['*']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $request->expires_at,
        ], 201);
    }

    /**
     * List all tokens for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get();

        return response()->json([
            'data' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            }),
        ]);
    }

    /**
     * Revoke specific token
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (!$token) {
            return response()->json([
                'message' => 'Token not found'
            ], 404);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token revoked successfully'
        ]);
    }

    /**
     * Revoke all tokens
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully'
        ]);
    }
}
```

---

### 3. Protected API Routes

**Route Configuration** (`routes/api.php`):
```php
<?php

use Illuminate\Support\Facades\Route;
use Inovector\Mixpost\Http\Controllers\Api;

Route::prefix('mixpost')->group(function () {
    // Public: Token generation
    Route::post('auth/tokens', [Api\ApiTokenController::class, 'create']);

    // Protected: Require authentication
    Route::middleware('auth:sanctum')->group(function () {
        // Token management
        Route::get('auth/tokens', [Api\ApiTokenController::class, 'index']);
        Route::delete('auth/tokens/{tokenId}', [Api\ApiTokenController::class, 'destroy']);
        Route::delete('auth/tokens', [Api\ApiTokenController::class, 'destroyAll']);

        // Posts
        Route::apiResource('posts', Api\PostsController::class)->parameters(['posts' => 'post:uuid']);
        Route::post('posts/{post:uuid}/schedule', [Api\PostsController::class, 'schedule']);
        Route::post('posts/{post:uuid}/publish', [Api\PostsController::class, 'publish']);
        Route::post('posts/{post:uuid}/duplicate', [Api\PostsController::class, 'duplicate']);

        // Media
        Route::apiResource('media', Api\MediaController::class)->parameters(['media' => 'media:uuid']);
        Route::post('media/download', [Api\MediaController::class, 'download']);

        // Accounts
        Route::apiResource('accounts', Api\AccountsController::class)->parameters(['accounts' => 'account:uuid']);

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

### 4. Middleware Configuration

**API Middleware Stack** (`app/Http/Kernel.php`):
```php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];
```

**Rate Limiting** (`app/Providers/RouteServiceProvider.php`):
```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
}
```

---

### 5. Token Usage Examples

**Generate Token**:
```bash
curl -X POST http://example.com/api/mixpost/auth/tokens \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "secret",
    "token_name": "n8n-integration"
  }'
```

**Response**:
```json
{
  "token": "1|abc123def456ghi789...",
  "token_type": "Bearer",
  "expires_at": null
}
```

**Use Token in Requests**:
```bash
curl -X GET http://example.com/api/mixpost/posts \
  -H "Authorization: Bearer 1|abc123def456ghi789..." \
  -H "Accept: application/json"
```

**Revoke Token**:
```bash
curl -X DELETE http://example.com/api/mixpost/auth/tokens/1 \
  -H "Authorization: Bearer 1|abc123def456ghi789..."
```

---

### 6. Token Abilities (Permissions)

**Define Abilities**:
```php
// Full access
$token = $user->createToken('admin-token', ['*']);

// Limited access
$token = $user->createToken('read-only', [
    'posts:read',
    'media:read',
    'accounts:read'
]);

$token = $user->createToken('post-creator', [
    'posts:create',
    'posts:read',
    'media:upload'
]);
```

**Check Abilities in Controllers**:
```php
public function store(Request $request)
{
    if (!$request->user()->tokenCan('posts:create')) {
        return response()->json([
            'message' => 'Insufficient permissions'
        ], 403);
    }

    // Proceed with creation...
}
```

**Middleware for Abilities**:
```php
Route::middleware(['auth:sanctum', 'abilities:posts:create'])->group(function () {
    Route::post('posts', [Api\PostsController::class, 'store']);
});
```

---

### 7. Token Storage & Security

**Database Schema** (`personal_access_tokens` table):
```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
);
```

**Security Best Practices**:
1. ✅ **Hash Tokens**: Sanctum automatically hashes tokens in database
2. ✅ **HTTPS Only**: Enforce HTTPS in production
3. ✅ **Token Rotation**: Implement periodic rotation policy
4. ✅ **Audit Logging**: Log token creation/usage/revocation
5. ✅ **IP Whitelisting**: Optional IP restriction per token
6. ✅ **Expiration**: Set expiration dates for temporary access

---

### 8. n8n Integration Setup

**n8n HTTP Request Node Configuration**:

1. **Method**: GET/POST/PUT/DELETE (based on endpoint)
2. **URL**: `https://your-domain.com/api/mixpost/posts`
3. **Authentication**: `Generic Credential Type`
   - **Type**: `Header Auth`
   - **Name**: `Authorization`
   - **Value**: `Bearer YOUR_TOKEN_HERE`
4. **Headers**:
   - `Accept: application/json`
   - `Content-Type: application/json`
5. **Body**: JSON payload for POST/PUT requests

**n8n Credential Setup**:
```json
{
  "name": "Mixpost API",
  "type": "httpHeaderAuth",
  "data": {
    "name": "Authorization",
    "value": "Bearer 1|abc123def456ghi789..."
  }
}
```

---

### 9. Error Handling

**Unauthenticated** (401):
```json
{
  "message": "Unauthenticated"
}
```

**Forbidden** (403):
```json
{
  "message": "This action is unauthorized"
}
```

**Invalid Token**:
```json
{
  "message": "Invalid or expired token"
}
```

**Rate Limited** (429):
```json
{
  "message": "Too many requests",
  "retry_after": 30
}
```

---

### 10. Testing Authentication

**PHPUnit Test Example**:
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthenticationTest extends TestCase
{
    public function test_can_generate_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/mixpost/auth/tokens', [
            'email' => 'test@example.com',
            'password' => 'password',
            'token_name' => 'test-token'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['token', 'token_type']);
    }

    public function test_can_access_protected_route_with_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(200);
    }

    public function test_cannot_access_without_token()
    {
        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(401);
    }

    public function test_can_revoke_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        Sanctum::actingAs($user, ['*']);

        $response = $this->deleteJson("/api/mixpost/auth/tokens/{$token->accessToken->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id
        ]);
    }
}
```

---

## 🎯 Summary

### Implementation Checklist
- ✅ Install Laravel Sanctum
- ✅ Migrate database tables
- ✅ Add HasApiTokens trait to User model
- ✅ Create API token controller
- ✅ Configure API routes with auth:sanctum middleware
- ✅ Implement token abilities/permissions
- ✅ Configure rate limiting
- ✅ Add security headers
- ✅ Test authentication flow
- ✅ Document for n8n users

### Key Benefits
- 🔒 Secure token-based authentication
- ⚡ Fast token validation
- 🎛️ Flexible permissions system
- 🔄 Easy token rotation
- 📊 Usage tracking
- 🌐 n8n compatible

---

**Document Version**: 1.0
**Last Updated**: 2025-10-23
