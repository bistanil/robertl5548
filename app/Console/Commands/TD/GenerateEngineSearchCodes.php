<?php

namespace App\Console\Commands\TD;

use Illuminate\Console\Command;
use App\Models\CarEngine;

class GenerateEngineSearchCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateenginesearchcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates searchable version for engine codes';

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
        $total = CarEngine::count();
        $limit = 10000;
        $offset = 0;
        while ($limit < $total)
        {
            $engines = CarEngine::where('td_id','>',0)->orderBy('id','asc')->limit($limit)->offset($offset)->get();
            foreach ($engines as $key => $engine) {
                echo $engine->id.' ';
                $engine->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $engine->code);
                $engine->save();
            }
            $offset += $limit;
        }
    }
}
