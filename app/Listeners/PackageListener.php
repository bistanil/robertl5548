<?php

namespace App\Listeners;

use App\Events\PackageDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\CategoryPart;
use App\Models\CarPart;
use App\Models\ModelPart;
use App\Models\TypePart;

class PackageListener
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
     * @param  PackageDelete  $event
     * @return void
     */
    public function delete(PackageDelete $event)
    {
        $product = $event->product;
        CategoryPart::wherePart_id($product->id)->delete();
        TypePart::wherePart_id($product->id)->delete();
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
