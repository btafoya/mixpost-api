<?php

namespace Btafoya\MixpostApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\PostQuery;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Http\Requests\StorePost;
use Inovector\Mixpost\Http\Requests\UpdatePost;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;
use Btafoya\MixpostApi\Http\Resources\PostResource;

class PostsController extends ApiController
{
    /**
     * List all posts with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            $request->input('per_page', config('mixpost-api.pagination.default_per_page', 20)),
            config('mixpost-api.pagination.max_per_page', 100)
        );

        $posts = PostQuery::apply($request)
            ->latest()
            ->latest('id')
            ->paginate($perPage);

        return PostResource::collection($posts)->response();
    }

    /**
     * Get a single post by UUID
     */
    public function show(string $uuid): JsonResponse
    {
        $post = Post::with(['accounts', 'versions', 'tags'])
            ->firstOrFailByUuid($uuid);

        return (new PostResource($post))->response();
    }

    /**
     * Create a new post
     */
    public function store(StorePost $request): JsonResponse
    {
        $post = $request->handle();
        $post->load(['accounts', 'versions', 'tags']);

        return (new PostResource($post))
            ->additional(['message' => 'Post created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing post
     */
    public function update(UpdatePost $request, string $uuid): JsonResponse
    {
        $post = Post::firstOrFailByUuid($uuid);

        // Set the post on the request for validation
        $request->post = $post;

        $post = $request->handle();
        $post->load(['accounts', 'versions', 'tags']);

        return (new PostResource($post))
            ->additional(['message' => 'Post updated successfully'])
            ->response();
    }

    /**
     * Delete a post
     */
    public function destroy(string $uuid): JsonResponse
    {
        $post = Post::firstOrFailByUuid($uuid);

        if ($post->isInHistory()) {
            return response()->json([
                'message' => 'Cannot delete posts that have already been published or failed'
            ], 422);
        }

        if ($post->isScheduleProcessing()) {
            return response()->json([
                'message' => 'Cannot delete posts that are currently being published'
            ], 422);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * Schedule a post for future publishing
     */
    public function schedule(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
        ]);

        $post = Post::with(['accounts', 'versions', 'tags'])
            ->firstOrFailByUuid($uuid);

        if ($post->isInHistory()) {
            return response()->json([
                'message' => 'Cannot schedule posts that have already been published or failed'
            ], 422);
        }

        if ($post->isScheduleProcessing()) {
            return response()->json([
                'message' => 'Cannot schedule posts that are currently being published'
            ], 422);
        }

        $scheduledAt = "{$request->date} {$request->time}";
        $post->setScheduled(Util::convertTimeToUTC($scheduledAt));

        return (new PostResource($post))
            ->additional(['message' => 'Post scheduled successfully'])
            ->response();
    }

    /**
     * Publish a post immediately (schedule for 30 seconds from now)
     */
    public function publish(string $uuid): JsonResponse
    {
        $post = Post::with(['accounts', 'versions', 'tags'])
            ->firstOrFailByUuid($uuid);

        if ($post->isInHistory()) {
            return response()->json([
                'message' => 'Cannot publish posts that have already been published or failed'
            ], 422);
        }

        if ($post->isScheduleProcessing()) {
            return response()->json([
                'message' => 'Cannot publish posts that are currently being published'
            ], 422);
        }

        if (! $post->accounts()->exists()) {
            return response()->json([
                'message' => 'Cannot publish posts without any accounts'
            ], 422);
        }

        // Schedule for immediate publishing (30 seconds from now)
        $post->setScheduled(now()->addSeconds(30));

        return (new PostResource($post))
            ->additional(['message' => 'Post queued for immediate publishing'])
            ->response();
    }

    /**
     * Duplicate an existing post
     */
    public function duplicate(string $uuid): JsonResponse
    {
        $post = Post::with(['accounts', 'versions', 'tags'])
            ->firstOrFailByUuid($uuid);

        $newPost = Post::create([
            'status' => PostStatus::DRAFT,
            'scheduled_at' => null,
        ]);

        // Copy relationships
        $newPost->accounts()->attach($post->accounts->pluck('id'));
        $newPost->tags()->attach($post->tags->pluck('id'));

        // Copy versions
        $newPost->versions()->createMany(
            $post->versions->map(fn ($v) => [
                'account_id' => $v->account_id,
                'is_original' => $v->is_original,
                'content' => $v->content
            ])->toArray()
        );

        $newPost->load(['accounts', 'versions', 'tags']);

        return (new PostResource($newPost))
            ->additional(['message' => 'Post duplicated successfully'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Bulk delete posts
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'posts' => 'required|array|min:1',
            'posts.*' => 'required|string',
        ]);

        $posts = Post::whereIn('uuid', $request->posts)
            ->where(function ($query) {
                $query->whereNotIn('status', [PostStatus::PUBLISHED->value, PostStatus::FAILED->value])
                    ->orWhereNull('status');
            })
            ->get();

        $deletedCount = 0;

        foreach ($posts as $post) {
            if (! $post->isScheduleProcessing()) {
                $post->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "{$deletedCount} posts deleted successfully"
        ]);
    }
}
