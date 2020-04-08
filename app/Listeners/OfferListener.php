<?php

namespace App\Listeners;

use App\Events\OfferDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OfferItem;

class OfferListener
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
     * @param  OfferDelete  $event
     * @return void
     */
    public function delete(OfferDelete $event)
    {
        OfferItem::whereOffer_id($event->offer->id)->delete();
    }
}
