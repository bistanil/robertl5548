<?php

namespace App\Listeners;

use App\Events\CatalogCategoryDelete;
use App\Events\CatalogCategoryUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\CatalogCategory;

class CatalogCategoryListener
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

    public function update(CatalogCategoryUpdate $event)
    {
        $category = $event->category;
        foreach ($category->products($category->id)->get() as $key => $product) {
            $product->active = $category->active;
            $product->save();
        }
    }

    /**
     * Handle the event.
     *
     * @param  CatalogCategoryDelete  $event
     * @return void
     */
    public function delete(CatalogCategoryDelete $event)
    {
        //                
        $this->deleteChildren($event->category);       
    }

    private function deleteChildren($category)
    {   
        $children=CatalogCategory::where('parent', $category->id)->get();
        if ($children->count()>0){
                foreach ($children as $child) 
                {
                    hwImage()->destroy($child->image, 'catalogCategory');                    
                    CatalogCategory::where('id', $child->id)->delete();
                    $this->deleteChildren($child);                       
                }
            }                        
    }
}