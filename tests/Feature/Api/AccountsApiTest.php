<?php

namespace Btafoya\MixpostApi\Tests\Feature\Api;

use Btafoya\MixpostApi\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inovector\Mixpost\Models\Account;
use Laravel\Sanctum\Sanctum;

class AccountsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createUser();
        Sanctum::actingAs($user, ['*']);
    }

    /** @test */
    public function it_can_list_accounts()
    {
        Account::factory()->count(3)->create();

        $response = $this->getJson('/api/mixpost/accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'uuid',
                        'name',
                        'username',
                        'provider',
                        'authorized',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_show_a_single_account()
    {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/mixpost/accounts/{$account->uuid}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $account->uuid,
                'name' => $account->name,
            ]);
    }

    /** @test */
    public function it_can_update_an_account()
    {
        $account = Account::factory()->create([
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/mixpost/accounts/{$account->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Account updated successfully']);

        $this->assertDatabaseHas('mixpost_accounts', [
            'id' => $account->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_delete_an_account()
    {
        $account = Account::factory()->create();

        $response = $this->deleteJson("/api/mixpost/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Account deleted successfully']);

        $this->assertDatabaseMissing('mixpost_accounts', [
            'id' => $account->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Don't set any authentication - test as unauthenticated user
        // Remove the setUp authentication by creating a fresh test instance without auth
        $this->app->forgetInstance('auth');

        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->getJson('/api/mixpost/accounts');

        $response->assertStatus(401);
    }
}
