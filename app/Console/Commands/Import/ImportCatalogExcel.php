<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Http\Libraries\ProductsImport;
use App\Models\Catalog;
use Excel;

class ImportCatalogExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-catalog-excel {catalogId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import catalog products from excel file';

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
        $catalog = Catalog::find($this->argument('catalogId'));
        Excel::load('public_html/public/files/import/productsImport.xlsx', function($reader) use ($catalog) {          
            $results = $reader->get();
            $import = new ProductsImport($reader, $catalog);
            $import->importProductInfo();            
        });
    }
}
