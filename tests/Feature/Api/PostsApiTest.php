<?php

namespace Btafoya\MixpostApi\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Models\Tag;
use Btafoya\MixpostApi\Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class PostsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate for all tests
        $user = $this->createUser();
        Sanctum::actingAs($user, ['*']);
    }

    /** @test */
    public function it_can_list_posts()
    {
        Post::factory()->count(5)->create();

        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'uuid',
                        'status',
                        'schedule_status',
                        'scheduled_at',
                        'published_at',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    /** @test */
    public function it_can_show_a_single_post()
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/mixpost/posts/{$post->uuid}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $post->uuid,
            ]);
    }

    /** @test */
    public function it_can_create_a_post()
    {
        $account = Account::factory()->create();

        $response = $this->postJson('/api/mixpost/posts', [
            'accounts' => [$account->id],
            'versions' => [
                [
                    'is_original' => true,
                    'account_id' => null,
                    'content' => [
                        [
                            'body' => 'Test post content',
                            'media' => []
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'uuid'],
                'message'
            ]);

        $this->assertDatabaseHas('mixpost_posts', [
            'status' => PostStatus::DRAFT->value,
        ]);
    }

    /** @test */
    public function it_can_update_a_post()
    {
        $post = Post::factory()->create();
        $account = Account::factory()->create();

        $response = $this->putJson("/api/mixpost/posts/{$post->uuid}", [
            'accounts' => [$account->id],
            'versions' => [
                [
                    'is_original' => true,
                    'account_id' => null,
                    'content' => [
                        [
                            'body' => 'Updated post content',
                            'media' => []
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post updated successfully']);
    }

    /** @test */
    public function it_can_delete_a_post()
    {
        $post = Post::factory()->create([
            'status' => PostStatus::DRAFT,
        ]);

        $response = $this->deleteJson("/api/mixpost/posts/{$post->uuid}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post deleted successfully']);

        $this->assertDatabaseMissing('mixpost_posts', [
            'id' => $post->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_published_posts()
    {
        $post = Post::factory()->create([
            'status' => PostStatus::PUBLISHED,
        ]);

        $response = $this->deleteJson("/api/mixpost/posts/{$post->uuid}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('mixpost_posts', [
            'id' => $post->id,
        ]);
    }

    /** @test */
    public function it_can_schedule_a_post()
    {
        $post = Post::factory()->create([
            'status' => PostStatus::DRAFT,
        ]);

        $response = $this->postJson("/api/mixpost/posts/{$post->uuid}/schedule", [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '10:00',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post scheduled successfully']);

        $post->refresh();
        $this->assertEquals(PostStatus::SCHEDULED, $post->status);
    }

    /** @test */
    public function it_can_publish_a_post_immediately()
    {
        $post = Post::factory()->create([
            'status' => PostStatus::DRAFT,
        ]);
        $account = Account::factory()->create();
        $post->accounts()->attach($account->id);

        $response = $this->postJson("/api/mixpost/posts/{$post->uuid}/publish");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post queued for immediate publishing']);

        $post->refresh();
        $this->assertEquals(PostStatus::SCHEDULED, $post->status);
        $this->assertNotNull($post->scheduled_at);
    }

    /** @test */
    public function it_can_duplicate_a_post()
    {
        $post = Post::factory()->create();
        $account = Account::factory()->create();
        $tag = Tag::factory()->create();

        $post->accounts()->attach($account->id);
        $post->tags()->attach($tag->id);
        $post->versions()->create([
            'is_original' => true,
            'account_id' => null,
            'content' => [['body' => 'Test', 'media' => []]],
        ]);

        $response = $this->postJson("/api/mixpost/posts/{$post->uuid}/duplicate");

        $response->assertStatus(201)
            ->assertJson(['message' => 'Post duplicated successfully']);

        $this->assertEquals(2, Post::count());
    }

    /** @test */
    public function it_can_bulk_delete_posts()
    {
        $posts = Post::factory()->count(3)->create([
            'status' => PostStatus::DRAFT,
        ]);

        $response = $this->deleteJson('/api/mixpost/posts', [
            'posts' => $posts->pluck('uuid')->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => '3 posts deleted successfully']);

        $this->assertEquals(0, Post::count());
    }

    /** @test */
    public function it_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/mixpost/posts');

        $response->assertStatus(401);
    }

    protected function createUser()
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::factory()->create();
    }
}
