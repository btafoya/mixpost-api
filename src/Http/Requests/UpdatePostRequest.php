<?php

namespace Btafoya\MixpostApi\Http\Requests;

use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Http\Requests\UpdatePost;
use Inovector\Mixpost\Models\Post;
use Inovector\Mixpost\Util;

class UpdatePostRequest extends UpdatePost
{
    public Post $post;

    /**
     * Override to use 'id' route parameter instead of 'post'
     */
    public function withValidator($validator)
    {
        // Use 'id' instead of 'post' since our routes use {id}
        $this->post = Post::findOrFail($this->route('id'));

        $validator->after(function ($validator) {
            if ($this->post->isInHistory()) {
                $validator->errors()->add('in_history', 'in_history');
            }

            if ($this->post->isScheduleProcessing()) {
                $validator->errors()->add('publishing', 'publishing');
            }
        });
    }

    /**
     * Override handle to return the Post model instead of boolean
     */
    public function handle(): Post
    {
        DB::transaction(function () {
            if (empty($this->input('accounts')) || !$this->scheduledAt()) {
                $this->post->setDraft();
            }

            $this->post->accounts()->sync($this->input('accounts'));
            $this->post->tags()->sync($this->input('tags'));

            $this->post->versions()->delete();
            $this->post->versions()->createMany($this->input('versions'));

            $this->post->update([
                'scheduled_at' => $this->scheduledAt() ? Util::convertTimeToUTC($this->scheduledAt()) : null,
            ]);
        });

        return $this->post;
    }
}
