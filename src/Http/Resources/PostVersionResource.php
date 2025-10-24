<?php

namespace Btafoya\MixpostApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostVersionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'is_original' => $this->is_original,
            'content' => $this->content,
        ];
    }
}
