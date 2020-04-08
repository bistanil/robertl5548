<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Http\Libraries\OEPart;
use App\Models\Supplier;
use Excel;
use Carbon\Carbon;
use File;


class ImportOEParts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-oe-parts {supplier_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from OE file';

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
        $supplier = Supplier::find($this->argument('supplier_id'));
        Excel::load('public_html/public/files/import/OEPartsImport.xlsx', function($reader) use ($supplier){            
            $sheets = $reader->get();
            foreach ($sheets as $sheet) {
                foreach ($sheet as $key => $result) {                                        
                    if ($key>0)
                    {
                        $copyPart = new OEPart($supplier);
                        $copyPart->process($result);                        
                    }
                }
            }
        });
    }
}
