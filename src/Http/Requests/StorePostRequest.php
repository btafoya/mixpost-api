<?php

namespace Btafoya\MixpostApi\Http\Requests;

use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Enums\PostStatus;
use Inovector\Mixpost\Http\Requests\StorePost;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;

class StorePostRequest extends StorePost
{
    /**
     * Override handle to refresh model after creation
     */
    public function handle(): Post
    {
        return DB::transaction(function () {
            $record = Post::create([
                'status' => PostStatus::DRAFT,
                'scheduled_at' => $this->scheduledAt() ? Util::convertTimeToUTC($this->scheduledAt()) : null
            ]);

            $record->accounts()->attach($this->input('accounts', []));
            $record->tags()->attach($this->input('tags'));
            $record->versions()->createMany($this->input('versions'));

            // Refresh to get all attributes including schedule_status
            return $record->fresh();
        });
    }
}
