<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Models\AutonetCode;
use App\Models\ProductPrice;
use App\Http\Libraries\Autonetws;

class ProcessAutonetCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-autonet-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get prices for the codes in Autonet list';

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
        ProductPrice::whereSource('autonet')->delete();
        $total = AutonetCode::count();
        $limit = 100;
        $offset = 0;
        while ($offset <= $total+$offset) {
            $codes = AutonetCode::orderBy('id')->limit($limit)->offset($offset)->get();   
            $process = new Autonetws();                       
            $process->processList($codes);      
            $offset += $limit;
        }
    }
}
