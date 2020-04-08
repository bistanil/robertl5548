<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Http\Libraries\PriceImport;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Supplier;
use App\Models\Company;
use Excel;
use Carbon\Carbon;
use File;
use Mail;
use DB;

class ImportPriceList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-price-list {supplier_id} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process price list excel file for a given supplier';

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
        $currency = Currency::whereDefault('yes')->get()->first();
        $supplier = Supplier::find($this->argument('supplier_id'));
        $source = str_slug($supplier->title, '_');
        $productPrices = Excel::load('/public_html/public/files/import/pricesImport.xlsx', function($reader) use ($currency, $supplier, $source){
        $sheets = $reader->get();
        foreach ($sheets as $sheet) {

            foreach ($sheet as $key => $item) {
                //dd($item);
                if (($item->manufacturer != '' || $item->manufacturer_nr) && $item->code != '' && $item->price > 0)
                {                    
                    if (isset($item->manufacturer)) $item->manufacturer = $this->correctManufacturerTitle($item->manufacturer);     
                    //dd($item->manufacturer);               
                    if (isset($item->manufacturer_nr))
                    {
                        $manufacturer = Manufacturer::whereManufacturer_nr(intval($item->manufacturer_nr))->get();
                        if ($manufacturer->count() > 0) $item->manufacturer = $manufacturer->first()->title;
                    } 
                    $company = Company::whereDefault('yes')->get()->first();
                    $tva = intval($company->vat_percentage)/100+1; 
                    $price['manufacturer'] = $item->manufacturer;
                    $price['code'] = preg_replace("/[^a-zA-Z0-9]+/","", str_replace('SAP-', '', $item->code));
                    $price['acquisition_price_no_vat'] = floatval(str_replace(',', '.',$item->acquisition_price));
                    $price['price_no_vat'] =floatval(str_replace(',', '.', $item->price));
                    $price['acquisition_price'] = floatval(str_replace(',', '.',$item->acquisition_price))*$tva;
                    $price['price'] = floatval(str_replace(',', '.',$item->price))*$tva;
                    $price['old_price'] = floatval(str_replace(',', '.',$item->old_price));
                    $price['stock_no'] = floatval(str_replace(',', '.',$item->stock_no));
                    $price['supplier_id'] = $supplier->id;  
                    DB::table('prices_temp')->insert($price);
                }                
            }
        }

        DB::update(DB::raw("INSERT INTO catalog_price_product(product_id, acquisition_price_no_vat, price_no_vat, acquisition_price, price, old_price, currency_id, stock_no, supplier_id, source, created_at, updated_at)
                                select catalog_products.id as product_id,
                                                prices_temp.acquisition_price_no_vat as acquisition_price,
                                                prices_temp.price_no_vat as acquisition_price,
                                                prices_temp.acquisition_price as acquisition_price,
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
        //DB::update(DB::raw("TRUNCATE TABLE prices_temp;"));  
        });        
        File::append('public_html/public/files/import/excelPriceListImportLog.txt', 'Import ended at '.Carbon::now()."\n");
        /*Mail::send('admin.emails.importFinished', [], function ($message) {
                    $message->from(config('mail.defaultEmail'), 'Admin');
                    $message->subject(trans('admin/emails.importFinishedTitle'));
                    $message->to($this->argument('email'));
                });*/
    }

    public function correctManufacturerTitle($title)
    {
        $title = str_replace('AL-KO (Alco-Kober)', 'AL-KO', $title);
        $title = str_replace('AP(LPR)', 'LPR', $title);
        $title = str_replace('ASSO ESAPAMENTE', 'ASSO', $title);
        $title = str_replace('AVA COOLING', 'AVA QUALITY COOLING', $title);
        $title = str_replace('Behr_Thermo-tronik', 'BEHR', $title);
        $title = str_replace('CLEAN FILTER', 'CLEAN FILTERS', $title);
        $title = str_replace('COOPERSFIAAM', 'COOPERSFIAAM FILTERS', $title);
        $title = str_replace('FAG (Kugelfischer)', 'FAG', $title);
        $title = str_replace('IMPERGOM', 'ORIGINAL IMPERIUM', $title);
        $title = str_replace('KAYABA', 'KYB', $title);
        $title = str_replace('Magneti-Marelli', 'MAGNETI MARELLI', $title);
        $title = str_replace('MANN+HUMMEL', 'MANN-FILTER', $title);
        $title = str_replace('MEAT&DORIA', 'MEAT & DORIA', $title);
        $title = str_replace('MEYLE Products', 'MEYLE', $title);
        $title = str_replace('NTN-SNR', 'SNR', $title);
        $title = str_replace('SILENCIO Valeo', 'VALEO', $title);
        $title = str_replace('TRW Automotive', 'TRW', $title); 
        $title = str_replace('Zimmermann (Otto, Bremse)', 'ZIMMERMANN', $title);
        $title = str_replace('BAUER PARTS', 'BTS Turbo', $title);
        $title = str_replace('A.I.C. - AM', 'AIC', $title);
        $title = str_replace('MTR-DEPO - AM', 'DEPO / LORO', $title);
        $title = str_replace('MTR - Caroserie', 'VAN WEZEL', $title);
        $title = str_replace('MTR - JRON', 'MTR', $title);
        $title = str_replace('MTR - Elemente Esapa', 'MTR', $title);
        $title = str_replace('MTR - AM', 'MTR', $title);
        $title = str_replace('LEMFOERDER', 'LEMFORDER', $title);
        $title = str_replace('VFMBosal', 'BOSAL', $title);
        $title = str_replace('LORO si DEPO', 'DEPO / LORO', $title);
        //$title = str_replace('LORO', 'DEPO / LORO', $title);
        $title = str_replace('DEPO', 'DEPO / LORO', $title);
        $title = str_replace('B CAR AUTO', 'B CAR', $title);
        $title = str_replace('PJ-VALEO', 'PJ', $title);
        $title = str_replace('A.I.C. Competition Line', 'AIC', $title);
        $title = str_replace('TESS', 'TESS CONEX', $title);
        return $title;
    }

}