<?php

namespace App\Console\Commands\TD;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;
use DB;

class GenerateProductGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateproductgroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate product group title for each product';

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
       $total = CatalogProduct::count();
       $limit = 10000;
       $offset = 0;
       while ($offset < $total) {
           $products = CatalogProduct::where('td_id', '>', 0)->orderBy('id', 'asc')->limit($limit)->offset($offset)->get();
           foreach ($products as $key => $product) {
               $info = DB::select(DB::raw('SELECT
                                    ART_ARTICLE_NR,
                                    SUP_BRAND,
                                    ART_SUP_ID,
                                    ART_ID,
                                    DES_TEXTS.TEX_TEXT AS ART_COMPLETE_DES_TEXT,
                                    DES_TEXTS2.TEX_TEXT AS ART_DES_TEXT,
                                    DES_TEXTS3.TEX_TEXT AS ART_STATUS_TEXT
                                FROM
                                               ARTICLES
                                    INNER JOIN DESIGNATIONS ON DESIGNATIONS.DES_ID = ART_COMPLETE_DES_ID
                                                           AND DESIGNATIONS.DES_LNG_ID = 21
                                    INNER JOIN DES_TEXTS ON DES_TEXTS.TEX_ID = DESIGNATIONS.DES_TEX_ID
                                     LEFT JOIN DESIGNATIONS AS DESIGNATIONS2 ON DESIGNATIONS2.DES_ID = ART_DES_ID
                                                                            AND DESIGNATIONS2.DES_LNG_ID = 21
                                     LEFT JOIN DES_TEXTS AS DES_TEXTS2 ON DES_TEXTS2.TEX_ID = DESIGNATIONS2.DES_TEX_ID
                                    INNER JOIN SUPPLIERS ON SUP_ID = ART_SUP_ID
                                    INNER JOIN ART_COUNTRY_SPECIFICS ON ACS_ART_ID = ART_ID
                                    INNER JOIN DESIGNATIONS AS DESIGNATIONS3 ON DESIGNATIONS3.DES_ID = ACS_KV_STATUS_DES_ID
                                                                            AND DESIGNATIONS3.DES_LNG_ID = 21
                                    INNER JOIN DES_TEXTS AS DES_TEXTS3 ON DES_TEXTS3.TEX_ID = DESIGNATIONS3.DES_TEX_ID
                                WHERE
                                    ART_ID = '.$product->td_id));
                if (isset($info[0])){
                    $product->product_group = ucfirst($info[0]->ART_COMPLETE_DES_TEXT);
                    $product->save();
                    echo $product->id.' ';
                }                
           }
          $offset += $limit;
       }
    }
}
