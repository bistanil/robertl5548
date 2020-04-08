<?php

namespace App\Listeners;

use App\Events\NewsCategoryDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PostCategory;

class NewsCategoryListener
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
     * @param  NewsCategoryDelete  $event
     * @return void
     */
    public function delete(NewsCategoryDelete $event)
    {
        //
        PostCategory::whereCategory_id($event->category->id)->delete();
    }
}
