<?php

function delete_form($routeParams, $label='Delete')
{
	$form = Form::open(['method' => 'DELETE', 'route' => $routeParams], ['name'=>'MyForm']);
	$form .= '<button type="submit" class="btn-options" id="deleteConfirmation"><i class="icon-bin"></i>'.$label.'</button>';
	$form .= Form::close();
	return $form;
}

function type_delete_form($routeParams, $label='Delete', $typeId, $productId)
{
	$form = Form::open(['method' => 'DELETE', 'route' => $routeParams], ['name'=>'MyForm']);
	$form .= Form::hidden('typeId', $typeId);
	$form .= Form::hidden('productId', $productId);
	$form .= '<button type="submit" class="btn-options" id="deleteConfirmation"><i class="icon-bin"></i>'.$label.'</button>';
	$form .= Form::close();
	return $form;
}

function flash($title = null, $message = null)
{
	$flash = app('App\Http\Libraries\Flash');

	if (func_num_args() == 0)
	{
		return $flash;
	}

	return $flash->info($title, $message);

}

function dropdownOptions($list, $items = null)
{
	$options=app('App\Http\Libraries\DropdownOptions');
	return $options->$list($items);
}

function hwImage($request = null, $type = null)
{		
	$image = app('App\Http\Libraries\Hwimage');		
	return $image;
}

function prepCode($code)
{
	$code=str_replace(' ', '', $code);
    $code=str_replace('.', '', $code);
    $code=str_replace('/', '', $code);
    $code=str_replace('-', '', $code);
    return $code;
}

function ancestors($item)
{
	$ancestors = app('App\Http\Libraries\Ancestors');
	return $ancestors->create($item);
}

function frontFlash($title = null, $message = null)
{
	$flash = app('App\Http\Libraries\FrontFlash');

	if (func_num_args() == 0)
	{
		return $flash;
	}

	return $flash->info($title, $message);
}

function defaultCurrency()
{
	$currency = app('App\Models\Currency');
	return $currency->defaultCurrency();
}

function imageExists($path, $type='product')
{
	if (!File::exists($path) || File::isDirectory($path)) return '/public/photos/nopic/no-'.$type.'.png';
	return $path;
}

function getLogo($type)
{
	$logo = app('App\Models\Logo');
	return $logo->whereActive('active')->whereLanguage(App::getLocale())->whereType('inEmail')->get()->first();
}

function partsSubcategoryPath($category)
{
	$subcategories = app('App\Models\PartsCategory');
	$subcategories = $subcategories->activeSubcategories($category->slug);
	if ($subcategories->count()>0) return route('front-parts-subcategories', ['typeSlug' => session()->get('type')->slug, 'slug' => $category->slug]);
	else return route('front-category-parts', ['typeSlug' => session()->get('type')->slug, 'categorySlug' => $category->slug]);
}

function partPath($part)
{
	if (session()->has('type')) return route('front-part-type', ['typeSlug' => session()->get('type')->slug, 'partSlug' => $part->slug]);
	else return route('front-type', ['slug' => $part->slug]);
}

function finalPrice($product)
{
	$finalPrice = app('App\Http\Libraries\Price');
	return $finalPrice = $finalPrice->finalPrice($product);
}

function salePrice($product, $price)
{
    $finalPrice = app('App\Http\Libraries\Price');
    return $finalPrice = $finalPrice->salePrice($product, $price);
}

function productPrice($product)
{
	$productPrice = app('App\Http\Libraries\Price');
	return $productPrice = $productPrice->productPrice($product);
}

function discountedPrice($product)
{
    $discountedPrice = app('App\Http\Libraries\Price');
    return $discountedPrice = $discountedPrice->discountedPrice($product);
}

function frontDeleteForm($routeParams, $label='Delete', $sizeClass=null)
{
	$form = Form::open(['method' => 'DELETE', 'route' => $routeParams], ['name'=>'MyForm']);
	$form .= '<button type="submit" class="btn btn-primary '.$sizeClass.'" id="deleteConfirmation">'.$label.'</button>';
	$form .= Form::close();
	return $form;
}

function productPath($product, $catalogId)
{
    if ($catalogId > 0) return route('front-product', ['slug' => $product->slug]);
    return route('front-part', ['slug' => $product->slug]);
}

function adminProductPath($product, $catalogId)
{
    if ($catalogId > 0) return route('admin-catalog-products.show', ['slug' => $product->slug]);
    return route('admin-part.show', ['slug' => $product->slug]);
}

function productTitle($product)
{
    $info = app('ProductInfo', ['product' => $product]);
    return $info->title();
}

function productTitleWithoutCode($product)
{
    $info = app('ProductInfo', ['product' => $product]);
    return $info->titleWithoutCode();
}

function cartTotal($instance = 'default')
{
	$cartExtension = app('App\Http\Libraries\CartExtension');
	$cartExtension->setInstance($instance);
	return $cartExtension->subtotal();
}

function autonetPrice($product)
{
	$autonet = app('App\Http\Libraries\Autonetws');
	$autonet->setProduct($product);
	$autonet->process();
}

function setActiveInactive($id, $status, $modelTitle){
	$model = app("App\Models\\".$modelTitle);
	$item = $model->find($id);
	if ($item != null) {
		$item->active = $status;
        $item->update();
        if($item->active == 'active') {
            return '<a id="setInactive'.$item->id.'" class="btn btn-danger" onclick="setActiveInactive('.$item->id.', \'inactive\', \''.$modelTitle.'\')">'.trans('admin/common.deactivate').'</a>';
        } else { 
            return '<a id="setActive'.$item->id.'" class="btn btn-primary" onclick="setActiveInactive('.$item->id.', \'active\', \''.$modelTitle.'\')">'.trans('admin/common.activate').'</a>';
        }
	}

}

function setApproveReject($id, $status, $modelTitle){
	$model = app("App\Models\\".$modelTitle);
	$item = $model->find($id);
	if ($item != null) {
		$item->status = $status;
        $item->update();
        if($item->status == 'approved') {
            return '<a id="setRejected'.$item->id.'" class="btn btn-danger" onclick="setApproveReject('.$item->id.', \'rejected\', \''.$modelTitle.'\')">'.trans('admin/common.reject').'</a>';
        } else { 
            return '<a id="setApproved'.$item->id.'" class="btn btn-primary" onclick="setApproveReject('.$item->id.', \'approved\', \''.$modelTitle.'\')">'.trans('admin/common.approve').'</a>';
        }
	}

}

function categoryRoute($categories, $subcategory)
{
	$subcategories = $categories->where('parent', $subcategory->id);
	if ($subcategories->count() > 0) return route('front-parts-subcategories',['typeSlug' => session()->get('type')->slug, 'slug' => $subcategory->slug]);
	else return route('front-category-parts', ['typeSlug' => session()->get('type')->slug, 'categorySlug' => $subcategory->slug]);
}

function listItemValue($attribute, $value)
{
	return $value;	
}

function manufacturer($manufacturerId)
{
	$manufacturer = app('App\Models\Manufacturer');
	$manufacturer = $manufacturer->find($manufacturerId);
	return $manufacturer;
}

function convertFormattedNumberToFloat($number)
{
	return floatval(str_replace(',', '', $number));
}

function checkSpecialText($product) 
{
	$productText = '';
	if($product->catalog_id != 0) {
		if ($product->categories()->count() > 0)	$productText = $product->categories()->get()->first()->category->special_info;
		else $productText = '';
	} else {
		$categories = $product->partsCategories()->orderBy('id','DESC')->get();
		foreach($categories as $key=>$category) {
			if($category->special_info != '') {
				$productText = $category->special_info;
				break;
			} else {
				$productText = '';
			}
		}
	}
	return $productText;

}

function checkImage($product) 
{
	$image = app('App\Http\Libraries\CheckPartCategoryImage');
	return $image->check($product);
}

function listItemId($listId, $value)
{
	$item = app('App\Models\CatalogListItem');
	$item = $item->whereList_id($listId)->whereValue($value)->get();
	if ($item->count() > 0) return $item->first()->id;
	return 0;
}

function isValidProduct($productId)
{
	$validator = app('App\Http\Libraries\ValidateProduct', [$productId]);
	return $validator->validate();
}

function getTransport($transportId)
{
	$transportMargin = app('App\Models\TransportMargin');
	return $transportMargin->where('min', '<=', floatval(str_replace(',', '', Cart::instance('shopping')->subtotal())))->where('max', '>=', floatval(str_replace(',', '', Cart::instance('shopping')->subtotal())))->where('type_id',$transportId)->get()->first();
}

function getCategory($product) {
	if($product->catalog_id == 0) {
        //$category = implode(' > ',$product->partsCategories->pluck('title')->toArray());
        $category = $product->partsCategories->sortByDesc('id')->first()->title;
        return $category;
    } else {
    	$category = app('App\Models\CatalogCategory');
    	$category = $category->find($product->categories()->get()->first()->category_id)->title;
        return $category;
    }
}

function getFeedCars($cars) 
{
	$cars = json_decode($cars);
	$carModel = app('App\Models\Car');
	$cars = $carModel->whereIn('id',$cars)->get();
	return $cars;
}

function getFeedModels($models) 
{
	$models = json_decode($models);
	$modelModel = app('App\Models\CarModel');
	$models = $modelModel->whereIn('id',$models)->get();
	return $models;
}

function getFeedTypes($types) 
{
	$types = json_decode($types);
	$typeModel = app('App\Models\CarModelType');
	$types = $typeModel->whereIn('id',$types)->get();
	return $types;
}

function getFeedManufacturers($manufacturers) 
{
	$manufacturers = json_decode($manufacturers);
	$manufacturerModel = app('App\Models\Manufacturer');
	$manufacturers = $manufacturerModel->whereIn('id',$manufacturers)->get();
	return $manufacturers;
}

function r_collect($array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $value = r_collect($value);
            $array[$key] = $value;
        }
    }
    return collect($array);
}

function getCatalogs($ids)
{
	$catalog = app('App\Models\Catalog');	
	return $catalog->whereIn('id', $ids)->get();
}

function checkProductFeed($productId,$feedId) {
	$productFeed = app('App\Models\ProductFeed');	
	$productFeed = $productFeed->where('product_id',$productId)->where('feed_id',$feedId)->get()->first();
	return $productFeed;
}


function validateProfileSection($profileSections, $group, $method)
{
	$useMethod = false;
	if ($method == 'create') $useMethod = true;
	if ($method == 'store') $useMethod = true;
	if ($method == 'edit') $useMethod = true;
	if ($method == 'update') $useMethod = true;
	if ($method == 'destroy') $useMethod = true;	
	if ($useMethod)
	{
		if($profileSections->filter(function ($item, $key) use ($group, $method){
			if ($item->group == $group && $item->method == $method) return $item;
		})->count() > 0) return true;	
		return false;
	}			
	if($profileSections->filter(function ($item, $key) use ($group){
		if ($item->group == $group) return $item;
	})->count() > 0) return true;	
	return false;	
}

function userHasPermission($route)
{
	$authorize = app()->makeWith('App\Http\Libraries\AdminAccessControl', ['route' => $route]);
	$authorize->setRoute($route);
	return $authorize->hasPermission();
}

function isValidImage($path)
{
	if (file_exists($path) == false) return false;
	// get mime type of file
    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);    	
    // define core
    switch (strtolower($mime)) {
        case 'image/png':
        case 'image/x-png': return true;
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg': return true;
        case 'image/gif': return true;
        case 'image/webp':
        case 'image/x-webp': return true;                
        default: return false; 
    }
    return false;
}

function isActiveRoute($route, $output = "active")
{
    if (Route::currentRouteName() == $route) return $output;
}

function areActiveRoutes(Array $routes, $output = "active")
{
    foreach ($routes as $route)
    {
        if (Route::currentRouteName() == $route) return $output;
    }

}

function getCarModels($carId){
	$car = app('App\Models\Car');
	$car = $car->find($carId);
	return $car->models()->where('car_models.active', '=', 'active')->get();
}

function getCarModelTypes($modelId){
	$model = app('App\Models\CarModel');
	$model = $model->find($modelId);
	return $model->types()->whereActive('active')->get();
}

function noCarPartsSubcategoryPath($category)
{
	$subcategories = app('App\Models\PartsCategory');
	$subcategories = $subcategories->noTypeActiveSubcategories($category->slug);
	if ($subcategories->count()>0) return route('front-no-car-parts-subcategories', ['categorySlug' => $category->slug]);
	else return route('front-no-car-category-parts-brands', ['categorySlug' => $category->slug]);
}

function countCategoryModelTypes($category, $model)
{
	$types = app('App\Http\Libraries\CheckCarModels');
	return $types->countModelCategoryTypes($category, $model);
}

function locale()
{
	return App::getLocale();
}

function schemaProduct($product)
{
    $schema = app('SchemaOrg');
    return $schema->product($product);
}

function frontProductPath($product, $catalogId)
{
    if ($catalogId > 0) return route('front-product', ['slug' => $product->slug]);
    return route('front-part', ['slug' => $product->slug]);
}