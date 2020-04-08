<?php

namespace App\Console\Commands\Delete;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;

class DeleteProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all images for products in the given selection.';

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
        $products = CatalogProduct::whereType('OE-bmw')->get();
        foreach ($products as $key => $product) {
            echo $product->id.' ';
            foreach ($product->images as $image) {
                if ($image->source == 'oe-part-url')
                {
                    hwImage()->destroy($image->image, 'product');
                    $image->delete();
                }                
            }
        }
    }
}
