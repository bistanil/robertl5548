<?php

namespace App\Listeners;

use App\Events\PartDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\TypePart;
use App\Models\PartCode;
use App\Models\PartOriginalCode;

class PartListener
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
     * @param  PartDelete  $event
     * @return void
     */
    public function delete(PartDelete $event)
    {
        $product=$event->product;
        TypePart::wherePart_id($product->id)->delete();
        PartCode::wherePart_id($product->id)->delete();
        PartOriginalCode::wherePart_id($product->id)->delete();
        $categories=$product->categories;
        foreach ($categories as $key => $category) {
            $category->delete();
        }
        $prices=$product->prices;
        foreach ($prices as $key => $price) {
            $price->delete();
        }
        $images=$product->images;
        foreach ($images as $key => $image) {
            hwImage()->destroy($image->image, 'product');
            $image->delete();
        }
    }
}
