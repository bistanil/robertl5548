<?php

namespace App\Console\Commands\Acl;

use Illuminate\Console\Command;
use App\Models\AccessControlSection;
use Route;

class UpdateAccessControlSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-access-control-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update access control table from application admin routes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Route::getRoutes() as $key => $route) {
            if (strrpos($route->getName(), 'admin') !== false && strrpos($route->getName(), 'login') == false && strrpos($route->getName(), 'Logout') == false) 
            {
                $item = $this->acsection($route->getName());
                $segments = explode('.', $route->getName());
                $item->group = $segments[0];
                $item->route_name = $route->getName();
                $item->method = $route->getActionMethod();
                $groupSegments = explode('-', $segments[0]);
                if ($item->id == '') 
                {
                    if (array_key_exists(1, $groupSegments)) $item->label = 'admin/acl.'.$groupSegments[1];
                    $item->parent = 'first_level';   
                    $item->show_actions = 'yes';
                }
                $item->save();
            }
        }
    }

    private function acsection($routeName)
    {
        $item = AccessControlSection::whereRoute_name($routeName)->get()->first();
        if ($item != null) return $item;
        return new AccessControlSection();
    }
}
