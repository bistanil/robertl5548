<?php

namespace App\Http\Libraries\Feeds;

use App\Http\Libraries\GetProductsFeed;
use App\Http\Libraries\Feeds\CatalogProductsFeed;
use App\Models\CatalogProduct;
use App\Models\CatalogCategory;
use App\Models\CatalogAttribute;
use App\Models\Feed;
use Storage;
use Mail;
use League\Csv\Writer;
use File;
use Excel;
use Config;
use App;


Class PieseAutoFeed{

  protected $request;

  public function __construct($request)
  { 
    $this->request = $request;
  } 

  public function generateFeed()
  {  
    $file = $this->createFile();    
    $request = $this->request;
    $provider = new CatalogProductsFeed($request);
    $total = $provider->getTotal();        
    //dd($provider->buildQuery()->toSql());
    echo $total.' ';
    $offset = 0;
    $limit = 50000;
    $writer = Writer::createFromPath($file, 'w+');     
    $writer->setDelimiter(';');  
    $writer->insertOne(['ID Produs', 'Denumire Produs', 'Categorii', 'Descriere produs', 'Moneda', 'Pret produs', 'Cantitate', 'Poza']);        
    while ($offset < $total+$limit) {
      $products = $provider->getProducts($limit, $offset);          
      foreach ($products as $key => $product) {
        echo $product->id.' ';
        $this->writeLine($writer, $product);
      }         
      $offset += $limit;
    }
    }

    private function writeLine($writer, $product)
    {
      $request = $this->request;
      $title = $product->title.' '.$product->code.' '.$request['description_title'];
      $category = getCategory($product);
      $description = '<ul><li><b>Denumire produs: </b>'.ucfirst($product->title).'</li>';
      if($product->manufacturer != null) $description .= '<li><b>Producator: </b>'.$product->manufacturer->title.'</li>';
      $description .= '<li><b>Cod produs: </b>'.$product->code.'</li></ul><br>';
      $description .= '<h5><b>Specificatii: </b></h5> <br>'.preg_replace("/&#?[a-z0-9]+;/i", "", $product->content);      
      $description .= $this->addAttributes($description, $product); 
      $description .= '<br><p>'.$request['description'].'</p>';
      $description = str_replace(' class=""table table-bordered table-responsive""', '', $description);
      $description = str_replace(PHP_EOL, '', $description);
      $currency = 'RON';
      if($product->prices->count() > 0) {
        $price = finalPrice($product);
      } else {
        $price = '';
      }
      $qty = '100';
      if($product->images->count() > 0) {
          $image = url(config('hwimages.product.destination').$product->images->sortBy('position')->first()->image);
      } else {
        $image = '';
      }
        if ($category != null)
        {
            $writer->insertOne([$product->part_id, $title, $category, $description, $currency, $price, $qty, $image]);                
        }
    }

    private function addAttributes($description, $product)
    {
      $productAttributes = CatalogAttribute::select('title', 'position', 'catalog_attribute_product.*')
                                             ->join('catalog_attribute_product', function($join) use ($product){
                                                $join->on('catalog_attribute_product.attribute_id', '=', 'catalog_attributes.id')
                                                     ->where('catalog_attribute_product.product_id', '=', $product->id);
                                             })
                                             ->orderBy('position')
                                             ->get();
      $description .= '<table class="table table-bordered table-responsive">';
      if($productAttributes->count() > 1)
      {
        foreach($productAttributes as $key => $attribute)
        {
          if ($attribute->value != null)
            $description .= '<tr><th>'.$attribute->title.'</th><td>'.$attribute->value.'</td></tr>';
        }        
      }                
      $description .= '</table>';
      return $description;
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