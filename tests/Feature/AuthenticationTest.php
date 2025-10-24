<?php

namespace Inovector\MixpostApi\Tests\Feature;

use Inovector\MixpostApi\Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function it_can_create_an_api_token_with_valid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/mixpost/auth/tokens', [
            'email' => 'john@example.com',
            'password' => 'secret123',
            'token_name' => 'My API Token',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_name',
                    'token_type',
                    'abilities',
                    'expires_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'token_name' => 'My API Token',
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function it_cannot_create_token_with_invalid_credentials(): void
    {
        $this->createUser([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/mixpost/auth/tokens', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
            'token_name' => 'My API Token',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);
    }

    /** @test */
    public function it_validates_required_fields_for_token_creation(): void
    {
        $response = $this->postJson('/api/mixpost/auth/tokens', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'token_name']);
    }

    /** @test */
    public function it_can_create_token_with_custom_abilities(): void
    {
        $user = $this->createUser([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/mixpost/auth/tokens', [
            'email' => 'john@example.com',
            'password' => 'secret123',
            'token_name' => 'Limited Token',
            'abilities' => ['posts:read', 'posts:write'],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'abilities' => ['posts:read', 'posts:write'],
                ],
            ]);
    }

    /** @test */
    public function it_can_list_user_tokens(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($user, 'Token 1');
        $this->createToken($user, 'Token 2');

        $response = $this->withToken($token)
            ->getJson('/api/mixpost/auth/tokens');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'abilities',
                        'last_used_at',
                        'expires_at',
                        'created_at',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_requires_authentication_to_list_tokens(): void
    {
        $response = $this->getJson('/api/mixpost/auth/tokens');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_delete_a_specific_token(): void
    {
        $user = $this->createUser();
        $token1 = $user->createToken('Token 1');
        $token2 = $user->createToken('Token 2');

        $response = $this->withToken($token2->plainTextToken)
            ->deleteJson("/api/mixpost/auth/tokens/{$token1->accessToken->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token deleted successfully',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token1->accessToken->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_non_existent_token(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($user);

        $response = $this->withToken($token)
            ->deleteJson('/api/mixpost/auth/tokens/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Token not found',
            ]);
    }

    /** @test */
    public function it_can_revoke_current_token(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('Test Token');

        $response = $this->withToken($token->plainTextToken)
            ->deleteJson('/api/mixpost/auth/tokens/current');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Current token revoked successfully',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    /** @test */
    public function it_can_access_health_endpoint_with_valid_token(): void
    {
        $user = $this->createUser();
        $token = $this->createToken($user);

        $response = $this->withToken($token)
            ->getJson('/api/mixpost/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    /** @test */
    public function it_cannot_access_protected_endpoints_without_token(): void
    {
        $response = $this->getJson('/api/mixpost/health');

        $response->assertStatus(401);
    }

    /**
     * Helper method to make authenticated requests with a token.
     */
    protected function withToken(string $token): self
    {
        return $this->withHeader('Authorization', "Bearer {$token}");
    }
}
