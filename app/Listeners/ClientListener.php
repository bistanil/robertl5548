<?php

namespace App\Listeners;

use App\Events\ClientDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\OrderDelete;
use App\Models\Order;

class ClientListener
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
     * @param  ClientDelete  $event
     * @return void
     */
    public function delete(ClientDelete $event)
    {
        //
        $client = $event->client;        
        foreach ($client->deliveryAddresses as $key => $address) {
            $address->delete();
        }
        foreach ($client->companies as $key => $company) {
            $company->delete();
        }
        foreach ($client->notes as $key => $note) {
            $note->delete();
        }
        foreach ($client->reviews as $key => $review) {
            $review->client_id = null;
            $review->name = $client->name;
            $review->email = $client->email;
            $review->save();
        }
        if(!empty($client->discounts)) {
            foreach ($client->discounts as $key => $discount) {
                $discount->delete();
            }
        }
        foreach ($client->cars as $key => $car) {
            $car->delete();
        }
        
        Order::whereClient_id($client->id)->update([
            'client_id' => 0,
            'client_name' => trans('admin/clients.anonClient'),
            'client_email' => trans('admin/clients.anonClient'),
            'client_phone' => trans('admin/clients.anonClient'),
            'client_delivery_address' => trans('admin/clients.anonClient'),
            'client_delivery_contact_person' => trans('admin/clients.anonClient'),
            'client_delivery_phone' => trans('admin/clients.anonClient')            
        ]);

        foreach ($client->images as $key => $image) {
            hwImage()->destroy($image->image, 'client');
            $image->delete();
        }

    }
}
