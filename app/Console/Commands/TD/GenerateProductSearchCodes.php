<?php

namespace App\Console\Commands\TD;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;

class GenerateProductSearchCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateproductsearchcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates searchable version for product codes';

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
        $total = CatalogProduct::count();
        $limit = 10000;
        $offset = 3383828;
        while ($limit < $total)
        {
            $parts = CatalogProduct::where('td_id','>',0)->orderBy('id','asc')->limit($limit)->offset($offset)->get();
            foreach ($parts as $key => $part) {
                echo $part->id.' ';
                $part->search_code = preg_replace("/[^a-zA-Z0-9]+/","", $part->code);
                //$part->content = '<table border="0" cellpadding="1" cellspacing="1" style="width:500px"><tbody>'.$part->content.'</tbody></table>';
                $part->save();
            }
            $offset += $limit;
        }
    }
}
