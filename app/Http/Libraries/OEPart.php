<?php

namespace App\Http\Libraries;

use App\Models\CatalogProduct;
use App\Models\CarModelType;
use App\Models\Manufacturer;
use App\Models\TypePart;
use App\Models\PartsCategory;
use App\Models\CategoryPart;
use App\Models\PartOriginalCode;
use App\Models\ProductImage;
use App\Models\PartCode;
use App\Models\Currency;
use App\Models\ProductPrice;
use App\Models\Watermark;
use App\Models\Supplier;
use App\Models\ModelPart;
use App\Models\Company;
use DB;
use Storage;
use File;
use Image;

Class OEPart{

	protected $supplier;

	public function __construct($supplier)
	{
		$this->supplier = $supplier;	
	}

	public function process($item)
	{			
		$manufacturer = $this->getManufacturer($item->manufacturer);
		$oldPart = $this->getOldPart($item);
		$part = $this->getPart($item, $manufacturer);
		if ($part->td_id == 0)
		{
			$part->slug = str_slug($item->title.'-'.$manufacturer->title.'-'.$item->code, '-');
			$part->manufacturer_id = $manufacturer->id;
			$part->code = $item->code;
			if ($item->title != '') $part->title = $item->title;
			else if ($oldPart != null) $part->title = $oldPart->title;
			$part->meta_title = $item->title.' | Cod '.$item->code;
			$part->meta_keywords = $item->title.' | Cod '.$item->code;
			$part->meta_description = $item->title.' | Cod '.$item->code;					
			if($item->active != null) $part->active = str_slug(str_replace(' ','', $item->active));
			else $part->active = 'active';			
			if($item->language != null) $part->language = $item->language;
			else $part->language = 'ro';
			if($item->offer != null) $part->offer = $item->is_offer;
			else $part->offer = 'no';
			if($item->first_page != null) $part->first_page = $item->show_on_first_page;
			else $part->first_page = 'no';
			if($item->stock != null) $part->stock = $item->stock;
			else $part->stock = 'in_stock';
			$part->short_description = $item->short_description;
			$part->type = 'OE-'.$manufacturer->slug;
			$part->content = $item->content;
			$part->search_code = preg_replace("/[^a-zA-Z0-9]+/","",$part->code);
			if ($oldPart != null) 
			{
				$part->content = $oldPart->content;
				$part->product_group = $oldPart->product_group;
			}
			$part->catalog_id = 0;
			if ($part->save())
			{
				$this->codes($oldPart, $part);
				$this->originalCodes($oldPart, $part);
				$this->types($oldPart, $part);
				$this->models($oldPart, $part);
				$this->categories($oldPart, $part);							
				$this->price($part, $item, $manufacturer);								
				$this->removePreviousTdImages($part);
				if ($item->td_image == 'yes') $this->saveTdImages($part, $oldPart);
				$this->processImages($item, $part);
				$this->processImagesByUrl($item, $part);	
				echo $part->id.' ';
			}
		}						
	}

	public function getManufacturer($title)
	{
		$manufacturer = Manufacturer::whereTitle($title)->get()->first();
		if ($manufacturer != null) return $manufacturer;
		return $this->createManufacturer($title);
	}

	public function createManufacturer($title)
	{		
		$manufacturer = new Manufacturer;
		$manufacturer->title = $title;
		$manufacturer->slug = str_slug($title, '-');
		$manufacturer->active = 'active';
		$manufacturer->language = 'ro';
		$manufacturer->meta_title = $title;
		$manufacturer->meta_keywords = $title;
		$manufacturer->meta_description = $title;
		$manufacturer->save();
		return $manufacturer;
	}

	public function getOldPart($item)
	{
		return CatalogProduct::join('part_codes', function ($join) use ($item) { 
                                        $join->on('catalog_products.id', '=', 'part_codes.part_id')
                                             ->where('part_codes.code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","",$item->code));
                                })
							->select('catalog_products.*')
                            ->orderBy('code')
                            ->get()                                
                            ->first();
	}

	public function getPart($item, $manufacturer)
	{
		$part = CatalogProduct::whereManufacturer_id($manufacturer->id)->whereSearch_code(preg_replace("/[^a-zA-Z0-9]+/", "", $item->code))->get()->first();
		if ($part != null) return $part;
		return new CatalogProduct();
	}

	public function exists($manufacturerId, $partCode)
	{
		return CatalogProduct::where('manufacturer_id', $manufacturerId)->where('code', $partCode)->first();
	}

	public function codes($oldPart, $part)
	{
		PartCode::wherePart_id($part->id)->delete();
		$link = new PartCode;
		$link->part_id = $part->id;
		$link->code = preg_replace("/[^a-zA-Z0-9]+/","",$part->code);
		$link->save();
		if ($oldPart != null)
			foreach ($oldPart->codes as $key => $code) {
				if ($code->code != $part->code)
				{
					$link = new PartCode;
					$link->part_id = $part->id;
					$link->code = $code->code;
					$link->save();
				}
			}
	}

	public function originalCodes($oldPart, $part)
	{
		PartOriginalCode::wherePart_id($part->id)->delete();
		if ($oldPart != null)
			foreach ($oldPart->originalCodes as $key => $code) {
				$link = new PartOriginalCode;
				$link->part_id = $part->id;
				$link->code = $code->code;
				$link->brand = $code->brand;
				$link->save();
			}
	}

	public function types($oldPart, $part)
	{
		TypePart::wherePart_id($part->id)->delete();
		if ($oldPart != null)
			foreach ($oldPart->typeLinks as $key => $type) {
				$link = new TypePart;
				$link->part_id = $part->id;
				$link->type_id = $type->type_id;
				$link->save();
			}
	}

	public function models($oldPart, $part)
	{
		ModelPart::wherePart_id($part->id)->delete();
		if ($oldPart != null)
			foreach ($oldPart->modelLinks as $key => $model) {
				$link = new ModelPart();
				$link->part_id = $part->id;
				$link->model_id = $model->model_id;
				$link->save();
			}
	}

	public function categories($oldPart, $part)
	{
		CategoryPart::wherePart_id($part->id)->delete();
		if ($oldPart != null)
			DB::select(DB::raw("INSERT INTO category_parts(category_id, part_id)
								SELECT category_id, ".$part->id." as part_id
								FROM category_parts
								WHERE part_id=".$oldPart->id));
	}

	public function price($part, $item)
	{
		//$currency = Currency::whereDefault('yes')->get()->first();
		$price = ProductPrice::whereProduct_id($part->id)->whereSupplier_id($this->supplier->id)->get()->first();
		if ($price == null) $price = $this->savePrice($part, $item);
		if ($price != null)
		{
			ProductPrice::whereProduct_id($part->id)->whereSupplier_id($this->supplier->id)->delete();
			$price = $this->savePrice($part, $item);
		}		
	}

	public function savePrice($part, $item)
	{
		$currency = new Currency();
		$currency = $currency->byCode($item->currency);
		$company = Company::whereDefault('yes')->get()->first();
		$tva = intval($company->vat_percentage)/100+1;
		$price = new ProductPrice();
		if ($currency != null) $price->currency_id = $currency->id;
		else $price->currency_id = 1;
		$price->supplier_id = $this->supplier->id;
		$price->source = $source = str_slug($this->supplier->title, '_');
		$price->acquisition_price_no_vat = floatval(str_replace(',', '.',$item->acquisition_price));
		$price->price_no_vat = floatval(str_replace(',', '.',$item->price));
		$price->old_price = floatval(str_replace(',', '.',$item->old_price));
		if($this->supplier->title == 'Admin')
		{
			$price->price = floatval(str_replace(',', '.',$item->acquisition_price));
			$price->acquisition_price = floatval(str_replace(',', '.',$item->price));
		}
		if($this->supplier->title != 'Admin')
		{
			$price->price = floatval(str_replace(',', '.',$item->price*$tva));
			$price->acquisition_price = floatval(str_replace(',', '.',$item->price*$tva));
		}
		$price->product_id = $part->id;		
		if ($price->price > 0) $price->save();
	}

	public function saveTdImages($part, $oldPart)
	{			
		foreach ($oldPart->images as $key => $oldImage) {
			if (Storage::disk('public')->has('photos/catalog/products/'.$part->id.'-'.$oldImage->image) == false)
			{
				Storage::disk('public')->copy('photos/catalog/products/'.$oldImage->image, 'photos/catalog/products/'.$part->id.'-'.$oldImage->image);
				$image = new ProductImage();
				$image->product_id = $part->id;
				$image->title = $oldImage->title;
				$image->active = 'active';
				$image->image = $part->id.'-'.$oldImage->image;
				$image->type = 'td-img-oe';
				$image->save();
			}			
		}
	}

	public function removePreviousTdImages($part)
	{
		foreach ($part->images as $key => $image) {
			if ($image->type == 'td-img-oe')
			{
				hwImage()->destroy($image->image, 'product');
				$image->delete();
			}
		}
	}

	public function processImages($item, $part)
	{		
		$images = explode('|', $item->image);
		$type = 'product';
		foreach ($images as $key => $image) {
			$imageTitle = str_replace(' ', '', $image);
			$path = base_path().'/public_html/public/files/productImages/'.$imageTitle;
			if (file_exists($path) && $imageTitle != '')
			{
				$img = Image::make($path)->heighten(config('hwimages.'.$type.'.height'), function ($constraint) {
		                    $constraint->upsize();
		                });
				$watermark = $this->watermark($type);
				if ($watermark!=null) $img->insert(config('hwimages.'.$type.'Watermark.destination').$watermark->image, 'center');
		        $img->save($path);
		        $image = new ProductImage();        
		        $image->image = time().'-'.$imageTitle;    
		        $image->active = 'active';    
		        $image->title = $part->title.' '.$imageTitle;
		        $image->product_id = $part->id;
		        $image->source = 'oe-part';
		        $image->save();
		        Storage::disk('public')->move('files/productImages/'.$imageTitle, 'photos/catalog/products/'.time().'-'.$imageTitle);							
			}
		}		
	}

	public function processImagesByUrl($item, $part)
	{		
		$images = explode(',', $item->image_url);
		if (count($images) > 0)
        {
            $type = 'product';
            foreach ($images as $key => $image) {
                $imageTitle = trim(preg_replace('/^.+[\\\\\\/]/', '', str_replace('%', '', $image)), ' ');
                if ($imageTitle != '')
                {
                    $path = '/home/garageauto/public_html/public/files/productImages/'.$imageTitle;
                    $fp = fopen($path, 'w');
                    set_time_limit(0); // unlimited max execution time
                    $options = array(
                      CURLOPT_FILE    => $fp,
                      CURLOPT_TIMEOUT =>  8, // set this to 8 hours so we dont timeout on big files
                      CURLOPT_URL     => $image,
                    );
                    $ch = curl_init();
                    curl_setopt_array($ch, $options);
                    curl_exec($ch);
                    curl_close($ch);
                    $path = 'files/productImages/'.$imageTitle;
                    chmod(public_path($path), 0777);
                    if (file_exists(public_path($path)) && $imageTitle != '')
                    {
                        if ($this->testImage(public_path($path)))
                        {
                            $img = Image::make(public_path($path))->heighten(config('hwimages.'.$type.'.height'), function ($constraint) {
                                        $constraint->upsize();
                                    });
                            $watermark=$this->watermark($type);
                            if ($watermark!=null) $img->insert(config('hwimages.'.$type.'Watermark.destination').$watermark->image, 'center');
                            $img->save(public_path($path));
                            $image = new ProductImage();        
                            $image->image = time().'-'.$imageTitle;  
                            $image->active = 'active'; 
                            $image->source = 'oe-part-url';   
                            $image->title = $part->title;
                            $image->product_id = $part->id;
                            $image->save();
                            echo $image->id.'-'.$part->id.' ';
                            if (!file_exists(public_path('photos/catalog/products/'.time().'-'.$imageTitle)))
                            Storage::disk('public')->move('files/productImages/'.$imageTitle, 'photos/catalog/products/'.time().'-'.$imageTitle);
                        }
                    }
                }
            }       
        }	
	}

	public function watermark($type)
	{
		$watermarkType = $type.'Watermark';
		return Watermark::where('type',$watermarkType)->first();
	}

	private function getExtension($link)
    {
        if ($this->isOkUrl($link))
        {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = explode('/', $finfo->buffer(file_get_contents($link)));
            return $type[count($type)-1];
        }       
    }

    private function isOkUrl($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 200) return true;
        return false;
    }

    private function testImage($path)
    {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);

        // define core
        switch (strtolower($mime)) {
            case 'image/png':
            case 'image/x-png':
                return true;
                break;

            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                return true;
                break;

            case 'image/gif':
                return true;
                break;

            case 'image/webp':
            case 'image/x-webp':
                return true;
                break;

            default:
               return false;
        }   
    }

}