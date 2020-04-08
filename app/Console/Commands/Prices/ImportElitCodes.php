<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Models\Elit\ElitCode;
use App\Models\CatalogProduct;
use App\Models\Manufacturer;
use Storage;
use Excel;
use File;
use Config;
use DB;

class ImportElitCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-elit-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import elit codes from txt file';

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
        $files = collect(File::files(Storage::disk('prices')->path('elit')))->sortByDesc(function ($file){
                        return $file->getCTime();
                    });
        $this->processList($files->first());
    }

    public function processList($file)
    {
        $line = str_getcsv($file, "\n");
            dd($line);
            $manufacturer = $this->correctManufacturerTitle($line[3]);
            dd($manufacturer);
            if ($manufacturer != null)
            {
                $part = CatalogProduct::whereManufacturer_id($manufacturer->id)->whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/","", $line[2]))->get()->first();
                if ($part != null)
                {                                
                    $elitCode = $this->getPart($part);
                    if($elitCode->part_id != $part->id){
                        $elitCode = new ElitCode();
                        $elitCode->part_id = $part->id;
                        $elitCode->elit_code = $part->code;
                        $elitCode->manufacturer_id = $manufacturer->id;
                        $elitCode->save();
                        echo $part->id.' ';
                    }
                }
            }   
                       
    }

    private function correctManufacturerTitle($title)
    {
        $title = str_replace('FEBI', 'FEBI BILSTEIN', $title);
        $title = str_replace('LEMFOERDER', 'LEMFORDER', $title);
        $title = str_replace('LEMFÖRDER', 'LEMFORDER', $title);
        $title = str_replace('LEMOEER', 'LEMFÖRDER', $title);
        return $title;
    } 

    private function getPart($part)
    {
        $slug = $part->slug;
        $part = ElitCode::wherePart_id($part->id)->get()->first();
        if ($part != null) return $part;
        return new ElitCode();
    }
}