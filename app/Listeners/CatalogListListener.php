<?php

namespace App\Listeners;

use App\Events\CatalogListDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\CatalogListItem;

class CatalogListListener
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
     * @param  CatalogListDelete  $event
     * @return void
     */
    public function delete(CatalogListDelete $event)
    {
        //
        CatalogListItem::where('list_id', $event->list->id)->delete();
    }
}
