<?php

namespace App\Console\Commands\Prices;

use Illuminate\Console\Command;
use nusoap_client;
use App\Models\Webservice;
use App\Models\ProductPrice;
use App\Models\Currency;
use DB;

class ProcessBennetWebservice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processbennetwebservice {id} {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets and saves the pricelist from Bennett';

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
        $webservice = new Webservice();
        $webservice=$webservice->find($this->argument('id'));
        $extClient = new nusoap_client ("http://info3.bennett-auto.ro/server.php" ,false,false,false,false,false,3000,3000);
        $param = array($this->argument('key'));
        $extResult = $extClient->call("listaCoduriDetalii",array($param));
        $priceList = file_get_contents($extResult['file']);
        $rows = explode(PHP_EOL, $priceList);
        foreach ($rows as $row) {
            $row = str_getcsv($row);
            if (isset($row[0]) && isset($row[3]) && isset($row[4]))
            if ($row[3] != '' && $row[0] != '' && $row[4] > 0)
            {   
                $price['manufacturer'] = $row[3];
                $price['code'] = preg_replace("/[^a-zA-Z0-9]+/","",$row[0]);
                $price['price'] = floatval(str_replace(',', '.',$row[4]))*1.19;
                DB::table('prices_temp')->insert($price);
            }
        }
        ProductPrice::whereSource('bennett')->delete();
        $currency = Currency::whereDefault('yes')->get()->first();
        DB::select(DB::raw("INSERT INTO catalog_price_product(product_id, price, currency_id, source, created_at, updated_at)
                                SELECT catalog_products.id as product_id,
                                             prices_temp.price as price,
                                             ".$currency->id." as currency_id,
                                             'bennett' as source,   
                                             NOW() as created_at,
                                             NOW() as updated_at
                                FROM prices_temp
                                INNER JOIN catalog_products on catalog_products.`search_code` = prices_temp.`code`
                                INNER JOIN manufacturers on prices_temp.manufacturer = manufacturers.title and catalog_products.manufacturer_id = manufacturers.id"));            
        DB::update(DB::raw("TRUNCATE TABLE prices_temp;"));        
    }
}
