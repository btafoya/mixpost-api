<?php

namespace Btafoya\MixpostApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_total' => $this->size_total,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->getUrl(),
            'conversions' => $this->conversions,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
