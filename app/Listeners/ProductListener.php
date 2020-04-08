<?php

namespace App\Listeners;

use App\Events\ProductDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductListener
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
     * @param  ProductDelete  $event
     * @return void
     */
    public function delete(ProductDelete $event)
    {
        //
        $product = $event->product;
        $categories = $product->categories;
        foreach ($categories as $key => $category) {
            $category->delete();
        }
        $attributes = $product->attributes;
        foreach ($attributes as $key => $attribute) {
            $attribute->delete();
        }
        $prices = $product->prices;
        foreach ($prices as $key => $price) {
            $price->delete();
        }
        $reviews = $product->reviews;
        foreach ($reviews as $key => $review) {
            $review->delete();
        }
        $images = $product->images;
        foreach ($images as $key => $image) {
            hwImage()->destroy($image->image, 'product');
            $image->delete();
        }
    }
}
