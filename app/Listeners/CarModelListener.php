<?php

namespace App\Listeners;

use App\Events\CarModelDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CarModelTypeDelete;

class CarModelListener
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
     * @param  CarModelDelete  $event
     * @return void
     */
    public function delete(CarModelDelete $event)
    {
        //
        $types = $event->model->types;
        foreach ($types as $key => $type) {
            event(new CarModelTypeDelete($type));
            $type->delete();
        }
    }
}
