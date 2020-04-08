<?php

namespace App\Console\Commands\Delete;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;
use App\Events\PartDelete;

class DeleteProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-some-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete products that follow certain condition set in the class';

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
        $products = CatalogProduct::whereType('oe-bmw')->get();        
        foreach ($products as $key => $product) {
            event(new PartDelete($product));
            $product->delete();
        }
    }
}
