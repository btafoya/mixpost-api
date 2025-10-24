<?php

namespace Btafoya\MixpostApi\Http\Controllers\Api;

use Btafoya\MixpostApi\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\MediaConversions\MediaImageResizeConversion;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\MediaUploader;

class MediaController extends ApiController
{
    /**
     * List all media with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            $request->input('per_page', config('mixpost-api.pagination.default_per_page', 20)),
            config('mixpost-api.pagination.max_per_page', 100)
        );

        $query = Media::query();

        // Search by filename
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $media = $query->latest()
            ->paginate($perPage);

        return MediaResource::collection($media)->response();
    }

    /**
     * Get a single media item by UUID
     */
    public function show(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        return (new MediaResource($media))->response();
    }

    /**
     * Upload a new media file
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi|max:'.config('mixpost.max_file_size', 102400),
        ]);

        $file = $request->file('file');

        $uploader = MediaUploader::fromFile($file)
            ->path(now()->format('Y/m'));

        // Only add conversions if image processing is available (not in test env)
        if (app()->bound('image')) {
            $uploader->conversions([
                MediaImageResizeConversion::name('thumb')->width(430),
            ]);
        }

        $media = $uploader->uploadAndInsert();

        return (new MediaResource($media))
            ->additional(['message' => 'Media uploaded successfully'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Download media from URL
     */
    public function download(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            // Download the file
            $response = Http::timeout(30)->get($request->url);

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Failed to download file from URL',
                ], 422);
            }

            // Get filename from URL or Content-Disposition header
            $filename = basename(parse_url($request->url, PHP_URL_PATH));
            if (empty($filename)) {
                $filename = 'downloaded_'.time();
            }

            // Get mime type
            $mimeType = $response->header('Content-Type') ?? 'application/octet-stream';

            // Determine file extension
            $extension = $this->getExtensionFromMimeType($mimeType);
            if (! str_contains($filename, '.')) {
                $filename .= '.'.$extension;
            }

            // Create temporary file
            $tempPath = sys_get_temp_dir().'/'.$filename;
            file_put_contents($tempPath, $response->body());

            // Create UploadedFile instance
            $uploadedFile = new UploadedFile(
                $tempPath,
                $filename,
                $mimeType,
                null,
                true // test mode, don't validate
            );

            // Upload using MediaUploader
            $uploader = MediaUploader::fromFile($uploadedFile)
                ->path(now()->format('Y/m'));

            // Only add conversions if image processing is available (not in test env)
            if (app()->bound('image')) {
                $uploader->conversions([
                    MediaImageResizeConversion::name('thumb')->width(430),
                ]);
            }

            $media = $uploader->uploadAndInsert();

            // Clean up temp file
            @unlink($tempPath);

            return (new MediaResource($media))
                ->additional(['message' => 'Media downloaded successfully'])
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download media: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a media item
     */
    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        // Delete files from storage
        $media->deleteFiles();

        // Delete database record
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Bulk delete media items
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'media' => 'required|array|min:1',
            'media.*' => 'required|integer',
        ]);

        $mediaItems = Media::whereIn('id', $request->media)->get();

        $deletedCount = 0;

        foreach ($mediaItems as $media) {
            $media->deleteFiles();
            $media->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => "{$deletedCount} media files deleted successfully",
        ]);
    }

    /**
     * Get file extension from MIME type
     */
    protected function getExtensionFromMimeType(string $mimeType): string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
        ];

        return $mimeMap[$mimeType] ?? 'jpg';
    }
}
