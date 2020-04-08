<?php

namespace App\Listeners;

use App\Events\SupplierDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ProductPrice;

class SupplierListener
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
     * @param  SupplierDelete  $event
     * @return void
     */
    public function delete(SupplierDelete $event)
    {
        $supplier = $event->supplier;
        ProductPrice::whereSupplier_id($supplier->id)->delete();
    }
}
