<?php

namespace App\Console\Commands\Delete;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;
use App\Events\ProductDelete;

class RemoveDuplicateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove-duplicate-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate products from a catalog';

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
        $items = CatalogProduct::whereCatalog_id(8)->get();
        foreach ($items as $key => $item) {
            $duplicates = CatalogProduct::whereSlug($item->slug)->orderBy('id', 'desc')->get();
            $duplicates->pop();
            foreach ($duplicates as $duplicate) {
                echo $duplicate->id.' ';
                event(new ProductDelete($duplicate));
                $duplicate->delete();
            }
        }
    }
}
