<?php

namespace App\Listeners;

use App\Events\CarModelsGroupDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CarModelDelete;

class CarModelsGroupListener
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
     * @param  CarModelsGroupDelete  $event
     * @return void
     */
    public function delete(CarModelsGroupDelete $event)
    {
        //
        $models = $event->modelsGroup->models;
        foreach ($models as $key => $model) {
            event(new CarModelDelete($model));
            hwImage()->destroy($model->image, 'carModel');
            $model->delete();
        }
    }
}
