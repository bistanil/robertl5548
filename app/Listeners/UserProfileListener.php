<?php

namespace App\Listeners;

use App\Events\UserProfileDelete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\UserProfileSection;

class UserProfileListener
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
     * @param  UserProfileDelete  $event
     * @return void
     */
    public function delete(UserProfileDelete $event)
    {
        UserProfileSection::whereProfile_id($event->profile->id)->delete();        
    }
}
