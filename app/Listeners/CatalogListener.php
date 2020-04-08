<?php

namespace App\Listeners;

use App\Events\CatalogDelete;
use App\Events\CatalogUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CatalogCategoryDelete;
use App\Events\CatalogListDelete;
use App\Events\ProductDelete;


class CatalogListener
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

    public function update(CatalogUpdate $event)
    {
        $catalog = $event->catalog;
        foreach ($catalog->categories($catalog->slug)->get() as $key => $category) {
            $category->slug = str_slug($catalog->slug.'-'.$category->title, '-');
            $category->save();
        }
    }

    /**
     * Handle the event.
     *
     * @param  CatalogDelete  $event
     * @return void
     */
    public function delete(CatalogDelete $event)
    {
        // delete categories
        $catalog=$event->catalog;
        $categories=$catalog->categories($catalog->slug)->get();
        foreach ($categories as $category) {
            event(new CatalogCategoryDelete($category));
            hwImage()->destroy($category->image, 'catalogCategory');
            $category->delete();
        }

        //delete lists
        $lists=$catalog->lists($catalog->slug)->get();
        foreach ($lists as $list) {
            event(new CatalogListDelete($list));
            $list->delete();
        }
        return TRUE;

        //delete attributes
        $attributes=$catalog->attributes;
        foreach ($attributes as $key => $attribute) {
            $attribute->delete();
        }

        //delete products
        $products=$catalog->products;
        foreach ($products as $key => $product) {
            event(new ProductDelete($product));
            $product->delete();
        }
    }
}
