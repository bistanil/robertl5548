<?php

namespace App\Listeners;

use App\Events\CarModelTypeDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CarModelTypeListener
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
     * @param  CarModelTypeDelete  $event
     * @return void
     */
    public function delete(CarModelTypeDelete $event)
    {
        //
    }
}
