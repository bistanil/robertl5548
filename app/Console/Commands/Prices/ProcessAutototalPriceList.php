<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use App\Http\Libraries\PriceImport;
use App\Models\Manufacturer;
use App\Models\CatalogProduct;
use App\Models\Supplier;
use App\Models\Currency;
use App\Models\Company;
use App\Models\ProductPrice;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Type;
use Carbon\Carbon;
use Storage;
use File;
use Mail;
use DB;

class ProcessAutototalPriceList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-autototal-price-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all price lists from public_html/public/files/autototal folder on server';

    protected $sheetHeader = [];

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
        $files = collect(File::files(Storage::disk('prices')->path('autototal')))->sortByDesc(function ($file){
                        return $file->getCTime();
                    });
        $this->processList($files->first(), 'autototal-ftp');
    }

    private function setProperties($sheet)
    {
        foreach ($sheet->getRowIterator() as $key => $row) {
            $this->sheetHeader = $row;
            return true;
        }
    }

    private function formatRow($row)
    {        
        $item = collect([]);        
        foreach ($this->sheetHeader->getCells() as $key => $component) {
            $property = strtolower(str_replace(' ', '_', $component->getValue()));            
            if ($property != '') $item->$property = $row->getCells()[$key]->getValue();
        }
        return $item;
    }

    private function processList($file, $source)
    {
        ProductPrice::whereSource($source)->delete();
        $currency = Currency::whereDefault('yes')->get()->first();
        $supplier = Supplier::whereTitle('Autototal FTP')->get()->first();
        $company = Company::whereDefault('yes')->get()->first();
        $tva = intval($company->vat_percentage)/100+1;
        $reader = ReaderEntityFactory::createReaderFromFile($file);
        $reader->setFieldDelimiter(';');
        $reader->open($file);
        foreach ($reader->getSheetIterator() as $sheet) {
            $this->setProperties($sheet);
            $counter = 0;
            foreach ($sheet->getRowIterator() as $key => $item) {
                if ($key > 1){
                    $cells = $item->getCells();                    
                    $item = $this->formatRow($item);
                    if ($item->sup_brand != NULL && $item->art_article_nr != NULL && $item->pret > 0)
                    { 
                        if (isset($item->sup_brand)) $item->sup_brand = $this->correctManufacturerTitle($item->sup_brand); 
                        $price['manufacturer'] = $item->sup_brand;
                        $price['code'] = preg_replace("/[^a-zA-Z0-9]+/","", str_replace('SAP-', '', $item->art_article_nr));
                        $price['acquisition_price_no_vat'] = floatval(str_replace(',', '.',$item->pret));
                        $price['acquisition_price'] = floatval(str_replace(',', '.',$item->pret))*$tva;
                        $price['price_no_vat'] = floatval(str_replace(',', '.',$item->pret));
                        $price['price'] = floatval(str_replace(',', '.',$item->pret))*$tva;
                        $price['supplier_id'] = $supplier->id;  
                        DB::table('prices_temp')->insert($price);
                        if ($counter >= 100000)
                        {
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
                            DB::table('prices_temp')->truncate();
                            //DB::update(DB::raw("TRUNCATE TABLE prices_temp;"));    
                            $counter = 0;                            
                        } else $counter++;
                    }
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
                            DB::table('prices_temp')->truncate();
        $reader->close();                                
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
        $title = str_replace('LORO', 'DEPO / LORO', $title);
        $title = str_replace('DEPO', 'DEPO / LORO', $title);
        $title = str_replace('B CAR AUTO', 'B CAR', $title);
        $title = str_replace('PJ-VALEO', 'PJ', $title);
        $title = str_replace('A.I.C. Competition Line', 'AIC', $title);
        $title = str_replace('TESS', 'TESS CONEX', $title);
        $title = str_replace('PAGID', 'HELLA', $title);
        $title = str_replace('HANS PRIES - TOPRAN', 'TOPRAN', $title);
        $title = str_replace('BTS-TURBOCOMPRESOARE', 'BTS Turbo', $title);
        $title = str_replace('ESAPAMENTE ASSO', 'ASSO', $title);
        $title = str_replace('CASTROL OIL', 'CASTROL', $title);
        $title = str_replace('ULEI CASTROL','CASTROL',$title);
        $title = str_replace('ULEI CASTROL CAMIOANE','CASTROL',$title);
        $title = str_replace('ULEI CASTROL GERMANIA','CASTROL',$title);
        $title = str_replace('ULEI CASTROL PROFESSIONAL','CASTROL',$title);
        $title = str_replace('ULEI MOTUL','MOTUL',$title);
        $title = str_replace('ULEI ELF','ELF',$title);
        $title = str_replace('ARAL OIL','ARAL',$title);
        $title = str_replace('ULEI BMW','BMW',$title);
        $title = str_replace('DACIA OIL','DACIA',$title);
        $title = str_replace('FORD OIL','FORD',$title);
        $title = str_replace('ULEI - OE','OPEL',$title);
        $title = str_replace('ULEI-OE','OPEL',$title);
        //$title = str_replace('ORIGINAL OIL','MAZDA',$title);
        //$title = str_replace('ORIGINAL OIL','KIA',$title);
        //$title = str_replace('ORIGINAL OIL','HONDA',$title);
        //$title = str_replace('ORIGINAL OIL','TOYOTA',$title);
        //$title = str_replace('ORIGINAL OIL','LEXUS',$title);
        //$title = str_replace('ORIGINAL OIL','MERCEDES',$title);
        //$title = str_replace('ORIGINAL OIL','BMW',$title);
        //$title = str_replace('ORIGINAL OIL','VOLKSWAGEN',$title);
        $title = str_replace('ULEI URANIA','URANIA',$title);
        $title = str_replace('TOTAL OIL','TOTAL',$title);
        $title = str_replace('CHAMPION OIL','CHAMPION',$title);
        $title = str_replace('ELF OIL','ELF',$title);
        $title = str_replace('MOBIL OIL','MOBIL',$title);
        $title = str_replace('ULEI - MOBIL','MOBIL',$title);
        $title = str_replace('ULEI-MOBIL','MOBIL',$title);
        $title = str_replace('ULEI ARAL','ARAL',$title);
        $title = str_replace('OPEL OIL','OPEL',$title);
        $title = str_replace('ULEI - TOTAL','TOTAL',$title);
        $title = str_replace('ULEI-TOTAL','TOTAL',$title);
        $title = str_replace('HEPU ANTIGEL','HEPU',$title);
        //New from others
        $title = str_replace('CAROSERIE AFTERMARKET','KLOKKERHOLM',$title);
        $title = str_replace('LEMOEER', 'LEMFÖRDER', $title);
        return $title;
    }
}