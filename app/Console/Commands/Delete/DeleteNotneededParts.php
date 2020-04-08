<?php

namespace App\Console\Commands\Delete;

use Illuminate\Console\Command;
use App\Models\Manufacturer;
use App\Models\ManufacturerImage;
use App\Models\CatalogProduct;
use App\Models\TypePart;
use App\Models\CategoryPart;
use App\Models\ProductImage;
use App\Models\PartCode;
use App\Models\PartOriginalCode;

class DeleteNotneededParts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-not-needed-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete not needed parts from inactive manufacturers';

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
        $manufacturers = Manufacturer::whereActive('inactive')->get();
        foreach ($manufacturers as $manufacturer) {
            echo $manufacturer->title.' ';
            $parts = CatalogProduct::where('td_id', '>', 0)->whereManufacturer_id($manufacturer->id)->get();
            foreach ($parts as $part) {
                CategoryPart::wherePart_id($part->id)->delete();
                TypePart::wherePart_id($part->id)->delete();
                $images = ProductImage::whereProduct_id($part->id)->get();
                foreach ($images as $image) {
                    hwImage()->destroy($image->image,'product');
                }
                ProductImage::whereProduct_id($part->id)->delete();
                PartCode::wherePart_id($part->id)->delete();
                PartOriginalCode::wherePart_id($part->id)->delete();
                $part->delete();
            }
            ManufacturerImage::whereManufacturer_id($manufacturer->id)->delete();
            $manufacturer->delete();
        }
    }
}
