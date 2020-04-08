<?php

namespace App\Listeners;

use App\Events\CountyDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CityDelete;

class CountyListener
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
     * @param  CountyDelete  $event
     * @return void
     */
    public function delete(CountyDelete $event)
    {
        $county=$event->county;
        //delete city
        $cities = $county->cities($county->id)->get();
        foreach ($cities as $city) {
            $city->delete();
        }
        return TRUE;
    }

}
