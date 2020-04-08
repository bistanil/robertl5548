<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Models\AutonetCode;
use App\Models\CatalogProduct;
use App\Models\Manufacturer;
use Excel;

class ImportAutonetCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-autonet-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import autonet codes from excel file';

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
        Excel::load('public_html/public/files/import/AutonetCodes.xlsx', function($reader){           
            $results = $reader->get();
            foreach ($results as $sheet) {
                foreach ($sheet as $key => $line) {                        
                    if ($line->manufacturer_nr != null)
                    {                        
                        $manufacturer = Manufacturer::whereManufacturer_nr($line->manufacturer_nr)->get()->first();
                        if ($manufacturer == null) $manufacturer = Manufacturer::whereTitle($line->manufacturer)->get()->first();
                        if ($manufacturer != null)
                        {
                            $part = CatalogProduct::whereManufacturer_id($manufacturer->id)->whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/","", $line->td_code))->get()->first();
                            if ($part == null) $part = CatalogProduct::whereManufacturer_id($manufacturer->id)->whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/","", $line->code))->get()->first();
                            if ($part != null)
                            {                                
                                $autonetCode = $this->getPart($part);
                                if($autonetCode->part_id != $part->id){
                                    $autonetCode = new AutonetCode();
                                    $autonetCode->part_id = $part->id;
                                    $autonetCode->code = $part->code;
                                    $autonetCode->manufacturer_id = $manufacturer->id;
                                    $autonetCode->save();
                                    echo $part->id.' ';
                                }
                            }
                        }
                    }                
                }
            }
        });
    }

    private function getPart($part)
    {
        $slug = $part->slug;
        $part = AutonetCode::wherePart_id($part->id)->get()->first();
        if ($part != null) return $part;
        return new AutonetCode();
    }
}