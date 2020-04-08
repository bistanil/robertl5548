<?php

namespace App\Listeners;

use App\Events\TransportTypeDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransportTypeListener
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
     * @param  TransportTypeDelete  $event
     * @return void
     */
    public function delete(TransportTypeDelete $event)
    {
        foreach ($event->transportType->margins as $key => $margin) $margin->delete();        
    }
}
