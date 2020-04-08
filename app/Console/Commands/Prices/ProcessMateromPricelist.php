<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Http\Libraries\PriceImport;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Company;
use App\Models\ProductPrice;
use App\Models\Supplier;
use Excel;
use Carbon\Carbon;
use File;
use Mail;
use DB;
use Storage;

class ProcessMateromPricelist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-materom-price-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Materom price list from FTP';

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
        $files = collect(File::files(Storage::disk('prices')->path('materom')))->sortByDesc(function ($file){
                        return $file->getCTime();
                    });
        $this->processList($files->first(), 'materom-ftp');
    }

    private function processList($file, $source)
    {
        if($source != null) ProductPrice::whereSource($source)->delete();
        $currency = Currency::whereDefault('yes')->get()->first();
        $supplier = Supplier::whereTitle('Materom FTP')->get()->first();
        $company = Company::whereDefault('yes')->get()->first();
        $tva = intval($company->vat_percentage)/100+1;
        config(['excel.import.startRow' => 2]);
        $productPrices = Excel::load($file, function($reader) use ($currency, $supplier, $company, $tva, $source){
        $sheets = $reader->get();
        foreach ($sheets as $sheet) {
            foreach ($sheet as $key => $item) {
                if (($item->brand != '' || $item->tecdoc) && $item->cod_npf != '' && $item->pret > 0)
                {                    
                    if ($item->tecdoc == '1977' && $item->brand == 'DEPO') $item->tecdoc = '4657';
                    if ($item->brand == 'MTR' && substr($item->cod_npf, 0, 2) != 'MT') $item->tecdoc = 36;
                    if ($item->tecdoc == '#N/A') $item->brand = 'OE';
                    if ($item->brand == 'A.I.C.') $item->brand = 'AIC';
                    if (isset($item->tecdoc))
                    {
                        $manufacturer = Manufacturer::whereManufacturer_nr(intval($item->tecdoc))->get();
                        if ($manufacturer->count() > 0) $item->brand = $manufacturer->first()->title;
                    }  
                    
                    $price['manufacturer'] = $item->brand;
                    $price['code'] = preg_replace("/[^a-zA-Z0-9]+/","", str_replace('SAP-', '', $item->cod_npf));
                    $price['acquisition_price_no_vat'] = floatval(str_replace(',', '.',$item->pret));
                    $price['acquisition_price'] = floatval(str_replace(',', '.',$item->pret))*$tva;
                    $price['price_no_vat'] = floatval(str_replace(',', '.',$item->pret));
                    $price['price'] = floatval(str_replace(',', '.',$item->pret))*$tva;
                    $price['supplier_id'] = $supplier->id;
                    DB::table('prices_temp')->insert($price);
                }                
            }
        }        
        DB::update(DB::raw("INSERT INTO catalog_price_product(product_id, acquisition_price_no_vat, acquisition_price, price_no_vat,price, old_price, currency_id, stock_no, supplier_id, source, created_at, updated_at)
                                select catalog_products.id as product_id,                                     
                                                  prices_temp.acquisition_price_no_vat as acquisition_price_no_vat,
                                                 prices_temp.acquisition_price as acquisition_price,
                                                 prices_temp.price_no_vat as price_no_vat,
                                                 prices_temp.price as price,
                                                 prices_temp.old_price as old_price,
                                                 ".$currency->id." as currency_id,
                                                 prices_temp.stock_no as stock_no,
                                                 ".$supplier->id." as supplier_id,
                                                 '".$source."' as source,
                                                 NOW() as created_at,
                                                 NOW() as updated_at
                                from catalog_products
                                inner join manufacturers on catalog_products.manufacturer_id=manufacturers.id
                                inner join prices_temp on prices_temp.manufacturer = manufacturers.title and catalog_products.search_code = prices_temp.code;"));     
        DB::update(DB::raw("TRUNCATE TABLE prices_temp;"));
        DB::update(DB::raw("delete
                            from catalog_price_product using catalog_price_product,
                                catalog_price_product t1
                            where catalog_price_product.id > t1.id
                                and catalog_price_product.product_id = t1.product_id
                                    and catalog_price_product.source = t1.source"));
        });       
    }
}