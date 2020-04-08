<?php

namespace App\Listeners;

use App\Events\OrderDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderListener
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
     * @param  OrderDelete  $event
     * @return void
     */
    public function delete(OrderDelete $event)
    {
        $order = $event->order;
        foreach ($order->items as $key => $item) {
            $item->delete();
        }

        foreach ($order->notes as $key => $note) {
            $note->delete();
        }

        foreach ($order->invoices as $key => $invoice) {
            hwImage()->destroy($invoice->docs, 'orderInvoice');
            $invoice->delete();
        }

        foreach ($order->warranties as $key => $warranty) {
            hwImage()->destroy($warranty->docs, 'orderWarranty');
            $warranty->delete();
        }
    }
}
