<?php

namespace Btafoya\MixpostApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'authorized' => $this->authorized,
            'image' => $this->image(),
            'data' => $this->data,
            'errors' => $this->when(isset($this->pivot), $this->pivot->errors ?? null),
            'provider_post_id' => $this->when(isset($this->pivot), $this->pivot->provider_post_id ?? null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
