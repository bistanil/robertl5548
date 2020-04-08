<?php

namespace App\Listeners;

use App\Events\CarDelete;
use App\Events\CarModelsGroupDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CarListener
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
     * @param  CarDelete  $event
     * @return void
     */
    public function delete(CarDelete $event)
    {
        //
        $modelsGroups = $event->car->modelsGroups();
        foreach ($modelsGroups as $key => $modelsGroup) {
            event(new CarModelsGroupDelete($modelsGroup));
            hwImage()->destroy($modelsGroup->image, 'carModelsGroup');
            $modelsGroup->delete();
        }
    }
}
