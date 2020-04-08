<?php

namespace App\Http\Libraries\Feeds;

use App\Http\Libraries\GetProductsFeed;
use App\Http\Libraries\Feeds\PartsFeed;
use App\Models\CatalogProduct;
use App\Models\CatalogCategory;
use App\Models\CarModelType;
use App\Models\Feed;
use Storage;
use Mail;
use League\Csv\Writer;
use File;
use Excel;
use Config;
use App;
use DB;

Class TDPieseAutoFeed{

  protected $request;

  public function __construct($request)
  { 
    $this->request = $request;
  } 

  public function generateFeed()
  {         
      $file = $this->createFile();    
      $request = $this->request;
      $provider = new PartsFeed($request);
      DB::update(DB::raw("TRUNCATE TABLE feed_lines;"));
      $select = $provider->buildQuery();       
      $bindings = $select->getBindings();
      $insertQuery = 'INSERT into feed_lines (part_id, active, product_id, catalog_id, manufacturer_title, product_meta_title, meta_keywords, meta_description, manufacturer_id, part_title, slug, code, car_title, stock, model_id, model_title, meta_title, construction_end_month, construction_end_year, construction_start_month, construction_start_year, content) '.$select->toSql();
      DB::insert($insertQuery, $bindings);      
      $offset = 0;
      $limit = 1000;
      $hasRows = true;
      $writer = Writer::createFromPath($file, 'w+');     
      $writer->setDelimiter(';');  
      $writer->insertOne(['ID Produs', 'Denumire Produs', 'Categorii', 'Descriere produs', 'Moneda', 'Pret produs', 'Cantitate', 'Poza']);        
      while ($hasRows) {
        //$products = $provider->getProducts($limit, $offset);                
        $products = DB::table('feed_lines')->limit($limit)->offset($offset)->get();
        if ($products->count() == 0) $hasRows = false;
        foreach ($products as $key => $product) {
          echo $product->product_id.' ';
          $this->writeLine($writer, $product);
        }         
        $offset += $limit;
      }
    }

    private function writeLine($writer, $product)
    {      
      $request = $this->request;
      $title = ucfirst($product->part_title).' '.$product->car_title.' '.$product->model_title.' ('.$product->construction_start_year.' - '.$product->construction_end_year.') '.$product->manufacturer_title.' '.$product->code.' '.$request['description_title'];      
      $description = '<ul><li><b>Denumire produs: </b>'.ucfirst($product->part_title).' '.$product->car_title.' '.$product->model_title.' ('.$product->construction_start_year.' - '.$product->construction_end_year.') '.$product->manufacturer_title. '</li>';
      $description .= '<li><b>Producator: </b>'.$product->manufacturer_title.'</li>';
      $description .= '<li><b>Cod produs: </b>'.$product->code.'</li></ul><br>';
      $description .= '<h5><b>Specificatii: </b></h5> <br>'.preg_replace("/&#?[a-z0-9]+;/i","",$product->content);
      $description = str_replace(' class=""table table-bordered table-responsive""', '', $description);
      $description = str_replace(PHP_EOL, '', $description);
      /*$types = CarModelType::whereModel_id($product->model_id)->get();
      $typesInfo = '<h5><b>Produsul se potriveste la urmatoarele motorizari:</b></h5><br><ul>';
      foreach ($types as $key => $type) {
        $typesInfo .= '<li>'.$product->car_title.' '.$product->model_title.' '.$type->title.' '.$type->kw.' KW/'.$type->hp.' CP ('.$type->construction_start_month.'.'.$type->construction_start_year.' - '.$type->construction_end_month.'.'.$type->construction_end_year.')';
        $engines = $type->engines;
        if ($engines->count() > 0)
        {
          $typesInfo .= ', cod motor: ';
          foreach ($engines as $engine) {
            $typesInfo .= $engine->code.',';
          }
        }
        $typesInfo .= '</li>';
      }
      $typesInfo .= '</ul>';
      $description .= $typesInfo.'<br>';*/
      $part_id = $product->part_id;
      $product = CatalogProduct::find($product->product_id);
      $category = getCategory($product);
      $originalCodes = $product->originalCodes()->whereBrand($product->car_title)->get();
      if($originalCodes->count() > 0) {
        $codeList = '<h5><b>Coduri originale: </b></h5><br><ul>';        
        foreach($originalCodes as $originalCode) {
           $codeList .= '<li>'.$originalCode->code.'</li>';
        }
        $codeList .= '</ul>';
      } else {
        $codeList = '';
      }
      $description .= '<br>'.$codeList.'<br><p>'.$request['description'].'</p>';
      $currency = 'RON';
      if($product->prices->count() > 0) $price = finalPrice($product);
      else $price = '';       
      $qty = '100';
      if($product->images->count() > 0) {
          $image = url(config('hwimages.product.destination').$product->images->sortBy('position')->first()->image);
      } else {
        $image = '';
      }
      if ($category != null)
      {
          $writer->insertOne([$part_id, $title, $category, $description, $currency, $price, $qty, $image]);                
      }
    }

    private function createFile()
    {
      $fileName = str_slug($this->request['file_name'], '-');
        $file_path = public_path('files/feeds/'.$fileName.'.csv');
        File::delete($file_path);
        if(! File::exists($file_path)){
            $excelFile = Excel::create($fileName, function ($csv) use($fileName)
            {
                $csv->setTitle($fileName);
                $csv->setCreator('StoAuto')->setCompany('StoAuto.');
                $csv->setDescription('Export al produselor din site conform modelului transmis');
                $csv->sheet('piese');
            })->store('csv', public_path('files/feeds'), true);
        }        
        return $file_path;
    }

}