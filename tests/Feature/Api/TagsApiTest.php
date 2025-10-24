<?php

namespace Btafoya\MixpostApi\Tests\Feature\Api;

use Btafoya\MixpostApi\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inovector\Mixpost\Models\Tag;
use Laravel\Sanctum\Sanctum;

class TagsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createUser();
        Sanctum::actingAs($user, ['*']);
    }

    /** @test */
    public function it_can_list_tags()
    {
        Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/mixpost/tags');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'hex_color',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_create_a_tag()
    {
        $response = $this->postJson('/api/mixpost/tags', [
            'name' => 'marketing',
            'hex_color' => '#FF5733',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'hex_color'],
                'message',
            ]);

        $this->assertDatabaseHas('mixpost_tags', [
            'name' => 'marketing',
            'hex_color' => '#FF5733',
        ]);
    }

    /** @test */
    public function it_validates_tag_name_is_unique()
    {
        Tag::factory()->create(['name' => 'existing-tag']);

        $response = $this->postJson('/api/mixpost/tags', [
            'name' => 'existing-tag',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_update_a_tag()
    {
        $tag = Tag::factory()->create([
            'name' => 'original-name',
        ]);

        $response = $this->putJson("/api/mixpost/tags/{$tag->id}", [
            'name' => 'updated-name',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tag updated successfully']);

        $this->assertDatabaseHas('mixpost_tags', [
            'id' => $tag->id,
            'name' => 'updated-name',
        ]);
    }

    /** @test */
    public function it_can_delete_a_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/mixpost/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tag deleted successfully']);

        $this->assertDatabaseMissing('mixpost_tags', [
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/mixpost/tags');

        $response->assertStatus(401);
    }

    protected function createUser()
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::factory()->create();
    }
}
