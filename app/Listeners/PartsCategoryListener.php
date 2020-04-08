<?php

namespace App\Listeners;

use App\Events\PartsCategoryDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PartsCategory;

class PartsCategoryListener
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
     * @param  PartsCategoryDelete  $event
     * @return void
     */
    public function delete(PartsCategoryDelete $event)
    {
        //                
        $this->deleteChildren($event->category);       
    }

    private function deleteChildren($category)
    {   
        $children=PartsCategory::where('parent', $category->id)->get();
        if ($children->count()>0){
                foreach ($children as $child) 
                {
                    hwImage()->destroy($child->image, 'partsCategory');                    
                    PartsCategory::where('id', $child->id)->delete();
                    $this->deleteChildren($child);                       
                }
            }                        
    }
}
