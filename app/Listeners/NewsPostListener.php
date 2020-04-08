<?php

namespace App\Listeners;

use App\Events\NewsPostDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PostCategory;
use App\Models\PostComment;

class NewsPostListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewsPostDelete  $event
     * @return void
     */
    public function delete(NewsPostDelete $event)
    {
        //
        PostCategory::wherePost_id($event->post->id)->delete();
        foreach ($event->post->images as $key => $image) {
            hwImage()->destroy($image->image, 'newsPost');
            $image->delete();
        }
        PostComment::wherePost_id($event->post->id)->delete();
    }
}
