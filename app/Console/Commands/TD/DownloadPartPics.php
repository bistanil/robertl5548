<?php

namespace App\Console\Commands\TD;

use Illuminate\Console\Command;
use App\Models\ProductImage;

class DownloadPartPics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-part-pics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download part pics from my server';

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
        $total = ProductImage::count();
        $limit = 10000;
        $offset = 0;
        while ($offset<=$total) {       
            echo ' offset: '.$offset.' total: '.$total;
            $images = ProductImage::orderby('id')->limit($limit)->offset($offset)->get();
            foreach ($images as $key => $image) {
                $fp = fopen('/home/garageauto/public_html/public/photos/catalog/products/'.$image->image, 'w');
                set_time_limit(0); // unlimited max execution time
                $options = array(
                  CURLOPT_FILE    => $fp,
                  CURLOPT_TIMEOUT =>  8, // set this to 8 hours so we dont timeout on big files
                  CURLOPT_URL     => 'http://-/dev/products/'.$image->image,
                );

                $ch = curl_init();
                curl_setopt_array($ch, $options);
                curl_exec($ch);
                curl_close($ch);
                echo $image->id.' ';
            }
            $offset += $limit;
        }       
    }
}