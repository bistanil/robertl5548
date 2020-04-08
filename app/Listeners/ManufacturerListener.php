<?php

namespace App\Listeners;

use App\Events\ManufacturerDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManufacturerListener
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
     * @param  ManufacturerDelete  $event
     * @return void
     */
    public function delete(ManufacturerDelete $event)
    {
        //
        $images=$event->manufacturer->images($event->manufacturer->slug);
        foreach ($images as $image) 
        {
            hwImage()->destroy($image->image, 'manufacturer');
            $image->delete();
        }
    }
}
