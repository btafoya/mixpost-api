<?php

namespace Btafoya\MixpostApi\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Models\Media;
use Btafoya\MixpostApi\Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $user = $this->createUser();
        Sanctum::actingAs($user, ['*']);
    }

    /** @test */
    public function it_can_list_media()
    {
        Media::factory()->count(5)->create();

        $response = $this->getJson('/api/mixpost/media');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'uuid',
                        'name',
                        'mime_type',
                        'size',
                        'url',
                        'created_at',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    /** @test */
    public function it_can_show_a_single_media()
    {
        $media = Media::factory()->create();

        $response = $this->getJson("/api/mixpost/media/{$media->uuid}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $media->uuid,
            ]);
    }

    /** @test */
    public function it_can_upload_media()
    {
        $file = UploadedFile::fake()->image('test.jpg', 600, 400);

        $response = $this->postJson('/api/mixpost/media', [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'uuid', 'name'],
                'message'
            ]);

        $this->assertDatabaseHas('mixpost_media', [
            'name' => 'test.jpg',
        ]);
    }

    /** @test */
    public function it_validates_file_upload()
    {
        $response = $this->postJson('/api/mixpost/media', [
            'file' => 'not-a-file',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_can_delete_media()
    {
        $media = Media::factory()->create();

        $response = $this->deleteJson("/api/mixpost/media/{$media->uuid}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Media deleted successfully']);

        $this->assertDatabaseMissing('mixpost_media', [
            'id' => $media->id,
        ]);
    }

    /** @test */
    public function it_can_bulk_delete_media()
    {
        $media = Media::factory()->count(3)->create();

        $response = $this->deleteJson('/api/mixpost/media', [
            'media' => $media->pluck('uuid')->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => '3 media files deleted successfully']);

        $this->assertEquals(0, Media::count());
    }

    /** @test */
    public function it_can_search_media_by_name()
    {
        Media::factory()->create(['name' => 'test-image.jpg']);
        Media::factory()->create(['name' => 'other-image.png']);

        $response = $this->getJson('/api/mixpost/media?search=test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/mixpost/media');

        $response->assertStatus(401);
    }

    protected function createUser()
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::factory()->create();
    }
}
