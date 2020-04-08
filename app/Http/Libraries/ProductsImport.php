<?php

namespace App\Http\Libraries;

use App\Models\Manufacturer;
use App\Models\CatalogProduct;
use App\Models\Currency;
use App\Models\ProductPrice;
use App\Models\CatalogCategory;
use App\Models\ProductCategory;
use App\Models\ProductAttribute;
use App\Models\Watermark;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\Company;
use DB;
use Image;
use Storage;
use Product;
use ProdPrice;

Class ProductsImport{

	protected $reader;
	protected $catalog;

	public function __construct($reader, $catalog)
	{
		$this->reader=$reader;
		$this->catalog=$catalog;
	}

	public function importProductInfo()
	{
		$results = $this->reader->get();		
		foreach ($results->first() as $key => $result) {			
			if ($key>0)
			{
				$title = str_slug(trans('admin/common.title'), "_");
				$code = str_slug(trans('admin/catalogs.code'), "_");				
				if ($result->$title != null && $result->$code != null)
				{
					$product = $this->getProduct($result);
					if ($product->id > 0) $product = Product::update($this->buildRequestArray($result), $product, true);
					else $product = Product::store($this->buildRequestArray($result), true);
					$this->importSearchCodes($product);
					$this->processImages($result, $product);
					$this->processImagesByUrl($result, $product);															
				}
			}
		}
		
	}

	private function buildRequestArray($result)
	{
		$request = [];
		$active = str_slug(trans('admin/common.active'), "_");
		$request['active'] = strtolower($result->$active);
		$title = str_slug(trans('admin/common.title'), "_");
		$request['title'] = $result->$title;	
		$code = str_slug(trans('admin/catalogs.code'), "_");
		$request['code'] = $result->$code;
		$request['slug'] = str_slug($request['title'].'-'.$request['code'], "-");			
		$meta_title = str_slug(trans('admin/common.meta_title'), "_");
		$request['meta_title'] = $result->$meta_title;
		if ($request['meta_title'] == '') $request['meta_title'] = $request['title'].' | Cod: '.$request['code'];
		$meta_keywords = str_slug(trans('admin/common.meta_keywords'), "_");
		$request['meta_keywords'] = $result->$meta_keywords;
		if ($request['meta_keywords'] == '') $request['meta_keywords'] = $request['title'].' | Cod: '.$request['code'];
		$meta_description = str_slug(trans('admin/common.meta_description'), "_");
		$request['meta_description'] = $result->$meta_description;
		if ($request['meta_description'] == '') $request['meta_description'] = 'Cumpara '.$request['title'];
		$offer = str_slug(trans('admin/catalogs.offer'), "_");
		$request['offer'] = $result->$offer;
		$first_page = str_slug(trans('admin/catalogs.firstPage'), "_");
		$request['first_page'] = $result->$first_page;
		$short_description = str_slug(trans('admin/catalogs.shortDescription'), "_");
		$request['short_description'] = $result->$short_description;
		$content = str_slug(trans('admin/common.content'), "_");
		$request['content'] = $result->$content;
		$stock = str_slug(trans('admin/catalogs.stock'), "_");
		$request['stock'] = $result->$stock;
		$request['catalog_id'] = $this->catalog->id;
		$request['type'] = 'catalog';
		$request['language'] = $this->catalog->language;
		$request['search_code'] = preg_replace("/[^a-zA-Z0-9]+/","", $request['code']);
		$request['import'] = 'yes';
		$manufacturerCol = str_slug(trans('admin/manufacturers.manufacturers'), "_");
		$manufacturer = $this->getManufacturer($result->$manufacturerCol);
		if (isset($manufacturer->id)) $request['manufacturer_id']=$manufacturer->id;
		$categoriesCol = str_slug(trans('admin/catalogs.categories'), "_");
		/*$weightCol = str_slug(trans('admin/catalogs.weightTitle'), '_');
		$request['weight'] = $result->$weightCol;
		$widthCol = str_slug(trans('admin/catalogs.widthTitle'), '_');
		$request['width'] = $result->$widthCol;
		$heightCol = str_slug(trans('admin/catalogs.heightTitle'), '_');
		$request['height'] = $result->$heightCol;
		$lengthCol = str_slug(trans('admin/catalogs.lengthTitle'), '_');
		$request['length'] = $result->$lengthCol;*/
		$request['categories'] = $this->setCategories(explode("|", $result->$categoriesCol));
		$request = $this->setAttributes($result, $request);	
		$request = $this->setPriceInfo($result, $request);
		//dd($request);
		return $request;
	}

	private function setCategories($categories)
	{
		$cats = [];
		foreach ($categories as $key => $category) {
			//$categorySlug = str_slug($this->catalog->title.' '.$category, "-");
			//$category = CatalogCategory::whereSlug($categorySlug)->whereCatalog_id($this->catalog->id)->get()->first();
			$category = CatalogCategory::whereTitle($category)->whereCatalog_id($this->catalog->id)->get()->first();
			if ($category != null) array_push($cats, $category->id);
		}
		return $cats;
	}

	private function getProduct($result)
	{
		$title = str_slug(trans('admin/common.title'), "_");
		$code = str_slug(trans('admin/catalogs.code'), "_");
		$slug = str_slug($result->$title.'-'.$result->$code, "-");
		$product = CatalogProduct::whereSlug($slug)->get()->first();
		if ($product != null) return $product;
		return new CatalogProduct();
	}

	private function getManufacturer($title)
	{
		$manufacturers = Manufacturer::whereTitle($title)->get();
		if ($manufacturers->count() > 0) return $manufacturers->first();
		return $this->createManufacturer($title);
	}

	private function createManufacturer($title)
	{
		$manufacturer = new Manufacturer();
		$manufacturer->title = $title;
		$manufacturer->slug = str_slug($title, '-');
		$manufacturer->meta_title = $title;
		$manufacturer->meta_keywords = $title;
		$manufacturer->meta_description = $title;
		$manufacturer->active = 'active';
		$manufacturer->language = $this->catalog->language;
		$manufacturer->save();
		return $manufacturer;
	}

	public function setPriceInfo($result, $request)
	{
		$company = Company::whereDefault('yes')->get()->first();
		$tva = intval($company->vat_percentage)/100+1;
		$currency = new Currency();
		$currencyCol = str_slug(trans('admin/currencies.currency'), "_");
		$currency = $currency->byCode($result->$currencyCol);
		if ($currency != null) $request['currency_id'] = $currency->id;
		else $request['currency_id'] = 1;
		$supplierCol = str_slug(trans('admin/catalogs.supplier'), "_");	
		$supplier = Supplier::whereTitle($result->$supplierCol)->get()->first();
		if ($supplier != null) $request['supplier_id'] = $supplier->id;
		else $request['supplier_id'] = 0;
		$request['source'] = str_slug($supplier->title, '_');
		$stockCol = str_slug(trans('admin/catalogs.stockNo'), "_");
		$request['stock_no'] = $result->$stockCol;	
		$acquisitionPriceCol = str_slug(trans('admin/catalogs.acquisitionPrice'), "_");
		$request['acquisition_price_no_vat'] = $result->$acquisitionPriceCol;
		$priceCol = str_slug(trans('admin/catalogs.price'), "_");
		$request['price_no_vat'] = $result->$priceCol;
		$priceCol = str_slug(trans('admin/catalogs.price'), "_");
		$oldPriceCol = str_slug(trans('admin/catalogs.oldPrice'), "_");
		$request['old_price'] = $result->$oldPriceCol;
		if($supplier->title == 'Admin')
		{
			$request['acquisition_price'] = floatval(str_replace(',', '.',$result->$acquisitionPriceCol));
			$request['price'] = floatval(str_replace(',', '.',$result->$priceCol));
		}
		if($supplier->title != 'Admin')
		{
			$request['acquisition_price'] = floatval(str_replace(',', '.',$result->$acquisitionPriceCol))*$tva;
			$request['price'] = floatval(str_replace(',', '.',$result->$priceCol))*$tva;					
		}
		return $request;
	}

	public function setAttributes($result, $request)
	{
		$attributes = $this->catalog->attributes($this->catalog->slug)->get();
		foreach ($attributes as $key => $attribute) {			
			$attributeCol = str_slug($attribute->title, "_");
			$request[$attribute->id] = $result->$attributeCol;			
		}
		return $request;
	}

	public function importSearchCodes($product)
	{
		DB::table('part_codes')->wherePart_id($product->id)->delete();
		DB::table('part_codes')->insert(
									['part_id' => $product->id, 'code' => $product->search_code]
								   );
	}

	public function processImages($line, $product)
	{
		$imagesCol = str_slug(trans('admin/common.image'), "_");
		$images = explode('|', $line->$imagesCol);
		$type = 'product';
		foreach ($images as $key => $image) {
			$imageTitle = str_replace(' ', '', $image);
			if ($imageTitle != '')
			{
				$path = 'files/productImages/'.$imageTitle;
				if (Storage::disk('public')->exists($path))
				{
					if (Storage::disk('public')->size($path) > 0)
					{
						try{
							$img = Image::make(Storage::disk('public')->path($path))->heighten(config('hwimages.'.$type.'.height'), function ($constraint) {
				                    $constraint->upsize();
				                });
							$watermark = $this->watermark($type);	
							if ($watermark != null) $img->insert('public_html/'.config('hwimages.'.$type.'Watermark.destination').$watermark->image, 'center');							
							$img->save(Storage::disk('public')->path($path));
					        $image = new ProductImage();        
					        $image->image = time().'-'.$imageTitle;  
					        $image->active = 'active';    
					        $image->title = $product->title.' '.$imageTitle;
					        $image->product_id = $product->id;
					        $image->source = 'catalog-'.$product->catalog->id;
					        $image->save();
					        Storage::disk('public')->move('files/productImages/'.$imageTitle, 'photos/catalog/products/'.time().'-'.$imageTitle);
						} catch(NotReadableException $e)
					    { continue; }
					}										
				}
			}			
		}		
	}

	public function processImagesByUrl($item, $product)
	{		
		$images = explode(',', $item->imagine_url);
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
                            $image->source = 'catalog-'.$product->catalog->id.'-from-url';   
                            $image->title = $product->title;
                            $image->product_id = $product->id;
                            $image->save();
                            echo $image->id.'-'.$product->id.' ';
                            if (!file_exists(public_path('photos/catalog/products/'.time().'-'.$imageTitle)))
                            Storage::disk('public')->move('files/productImages/'.$imageTitle, 'photos/catalog/products/'.time().'-'.$imageTitle);
                        }
                    }
                }
            }       
        }	
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

	public function watermark($type)
	{
		$watermarkType = $type.'Watermark';
		return Watermark::where('type', $watermarkType)->first();
	}

}