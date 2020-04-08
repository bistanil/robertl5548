<?php

namespace App\Listeners;

use App\Events\CatalogAttributeDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ProductAttribute;

class CatalogAttributeListener
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
     * @param  CatalogAttributeDelete  $event
     * @return void
     */
    public function delete(CatalogAttributeDelete $event)
    {
        ProductAttribute::whereAttribute_id($event->attribute->id)->delete();
    }
}
