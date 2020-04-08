<?php

namespace App\Listeners;

use App\Events\CurrencyDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CurrencyListener
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
     * @param  CurrencyDelete  $event
     * @return void
     */
    public function delete(CurrencyDelete $event)
    {
        //
    }
}
