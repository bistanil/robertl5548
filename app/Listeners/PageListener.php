<?php

namespace App\Listeners;

use App\Events\PageDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Page;

class PageListener
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
     * @param  PageDelete  $event
     * @return void
     */
    public function delete(PageDelete $event)
    {
        //        
        $this->deleteChildren($event->page);       
    }

    private function deleteChildren($page)
    {   
        $children=Page::where('parent', $page->id)->get();
        if ($children->count()>0){
                foreach ($children as $child) 
                {                    
                    Page::where('id', $child->id)->delete();
                    $this->deleteChildren($child);                       
                }
            }                        
    }
}
