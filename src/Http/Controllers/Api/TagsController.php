<?php

namespace Btafoya\MixpostApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inovector\Mixpost\Models\Tag;
use Btafoya\MixpostApi\Http\Resources\TagResource;

class TagsController extends ApiController
{
    /**
     * List all tags
     */
    public function index(): JsonResponse
    {
        $tags = Tag::latest()->get();

        return TagResource::collection($tags)->response();
    }

    /**
     * Create a new tag
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:mixpost_tags,name',
            'hex_color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
            'hex_color' => $request->hex_color,
        ]);

        return (new TagResource($tag))
            ->additional(['message' => 'Tag created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a tag
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:mixpost_tags,name,' . $id,
            'hex_color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = Tag::findOrFail($id);

        $tag->update($request->only(['name', 'hex_color']));

        return (new TagResource($tag))
            ->additional(['message' => 'Tag updated successfully'])
            ->response();
    }

    /**
     * Delete a tag
     */
    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully'
        ]);
    }
}
