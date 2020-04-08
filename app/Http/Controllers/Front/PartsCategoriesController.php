<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use JavaScript;
use Carbon\Carbon;
use DB;
use URL;
use App\Http\Libraries\Meta;
use App\Models\PartsCategory;
use App\Models\CarModelType;
use App\Http\Libraries\PartCategoriesTree;
use Illuminate\Http\Request;
use App\Models\CarModelGroup;
use App\Models\CarModel;
use App\Models\Car;
use App\Models\TransportMargin;
use App\Models\Contact;
use Cart;

class PartsCategoriesController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}	

	public function index($slug, CarModelType $type)
	{	
		$type = $type->bySlug($slug);
		session()->put('type', $type);
		$categories = collect(DB::select(DB::raw("SELECT DISTINCT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent>=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' WHERE parts_categories.id != 72 ORDER BY position")));
		$breadcrumb = 'frontPartsCategories';
		$meta = Meta::build(null, $type);
		$item = $type;
		return view('front.partials.products.parts.categories.main', compact('meta','breadcrumb','categories','type', 'item'));
	}

	public function withCarSubcategories($typeSlug, $categorySlug, CarModelType $type, PartsCategory $category)
	{	
		$type = $type->bySlug($typeSlug);
		$category = $category->bySlug($categorySlug);
		$categories = collect(DB::select(DB::raw("SELECT DISTINCT parts_categories.slug,
                                                 parts_categories.image,
                                                 parts_categories.title,
                                                 parts_categories.parent,
                                                 parts_categories.id
                                    FROM parts_categories
                                    INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent=".$category->id." AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' ORDER BY position")));
		$breadcrumb = 'frontPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		return view('front.partials.products.parts.categories.subcategories', compact('meta', 'breadcrumb', 'categories', 'type', 'category', 'item'));
	}

	public function subcategories($categorySlug, PartsCategory $category)
	{	
		$category = $category->bySlug($categorySlug);	
		session()->forget('manufacturerOrder');
		$categories = $category->noTypeActiveSubcategories($categorySlug);
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		return view('front.partials.products.parts.nocar.subcategories', compact('meta', 'breadcrumb', 'categories', 'category', 'item', 'products'));
	}

	public function search($slug, CarModelType $type, Request $request)
	{	
		$type = $type->bySlug($slug);
        if($type->engines->count() > 0) {
            foreach($type->engines as $engine) {
                if(!empty($engine)) {
                    $engine = $engine->code;
                } else {
                    $engine = ' ';
                }    
            }
        } else {
            $engine = ' ';
        }
        session()->put('type', $type);
        $query = "SELECT parts_categories.slug,
                         parts_categories.image,
                         parts_categories.title,
                         parts_categories.parent,
                         parts_categories.id
                FROM parts_categories
                INNER JOIN type_categories ON parts_categories.id=type_categories.category_id AND parts_categories.parent>=1 AND type_categories.type_id=".$type->id." AND parts_categories.active = 'active' ";
        $searchItems = explode(' ', $request->search);
        $first = true;
		foreach ($searchItems as $key => $search) {
			if ($first == true)
			{
				$query .= " WHERE title LIKE '%".$search."%' OR terms LIKE '%".$search."%'";
				$first = false;	
			} else $query .= " OR title LIKE '%".$search."%' OR terms LIKE '%".$search."%'";			
		}
		$query .= " ORDER BY position";
		$categories = collect(DB::select(DB::raw($query)));
		$categories = new PartCategoriesTree($categories, true);
		$categories = $categories->buildTree();		
		$breadcrumb = 'frontPartsCategories';

		$meta = Meta::build(null, $type);
		$item = $type;
		$search = $request->search;

		return view('front.partials.products.parts.categories.search', compact('meta','breadcrumb','categories','type', 'item', 'engine', 'search'));
	}

	public function selectCar($categorySlug,PartsCategory $category) {
        if (session()->has('type')) return redirect(route('front-category-parts', ['typeSlug' => session()->get('type')->slug, 'categorySlug' => $categorySlug]));
        $category = $category->bySlug($categorySlug);
        //session()->put('partCategory',$category);
        $brands = Car::whereActive('active')->orderBy('title')->get();
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		return view('front.partials.products.parts.categories.noCar.brands', compact('meta','breadcrumb','category','brands', 'item'));
    }

    public function selectCarModels($brandSlug, Car $car, $categorySlug, PartsCategory $category)
    {
    	if (session()->has('type')) return redirect(route('front-category-parts', ['typeSlug' => session()->get('type')->slug, 'categorySlug' => $categorySlug]));
    	$car = $car->bySlug($brandSlug);
    	$category = $category->bySlug($categorySlug);
		$modelGroups = CarModelGroup::whereCar_id($car->id)->whereActive('active')->orderBy('position')->get();	
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		return view('front.partials.products.parts.categories.noCar.models', compact('meta','breadcrumb','category','modelGroups', 'item', 'car'));
    }

    public function selectCarModelsType($modelSlug, CarModel $model, $categorySlug, PartsCategory $category)
    {
    	$category = $category->bySlug($categorySlug);
		$model = $model->bySlug($modelSlug);
		$types = CarModelType::whereModel_id($model->id)->whereActive('active')->orderBy('fuel','asc')->orderBy('hp','asc')->orderBy('position')->paginate();
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		return view('front.partials.products.parts.categories.noCar.types', compact('meta','breadcrumb','category','types', 'item', 'model'));
    }

    public function categoryParts($categorySlug, PartsCategory $category)
	{
		if (session()->has('type'))	return redirect(route('front-category-parts', ['typeSlug' => session()->get('type')->slug, 'categorySlug' => $categorySlug]));
		$category = $category->bySlug($categorySlug);
		$categories = $category->noTypeActiveSubcategories($categorySlug);
		$brands = Car::whereActive('active')->orderBy('position')->get();
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		$total = str_replace(',', '', Cart::instance('shopping')->subtotal());
        $transport = TransportMargin::where('margin','!=',0)->get()->first()->max;
		$contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
		return view('front.partials.products.parts.nocar.selectCar', compact('meta', 'breadcrumb', 'categories', 'category', 'item', 'products', 'brands','contactInfo', 'total', 'transport'));
	}

	public function categoryPartsModels($categorySlug, PartsCategory $category, $brandSLug, Car $car)
	{	
		$category = $category->bySlug($categorySlug);
		$car = $car->bySlug($brandSLug);
		$categories = $category->noTypeActiveSubcategories($categorySlug);
		$modelGroups = CarModelGroup::whereCar_id($car->id)->whereActive('active')->orderBy('position')->get();		
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		$total = str_replace(',', '', Cart::instance('shopping')->subtotal());
        $transport = TransportMargin::where('margin','!=',0)->get()->first()->max;
		$contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
		return view('front.partials.products.parts.nocar.selectModel', compact('meta', 'breadcrumb', 'categories', 'category', 'item', 'car', 'modelGroups','contactInfo', 'total', 'transport'));
	}

	public function categoryPartsTypes($categorySlug, PartsCategory $category, $modelSlug, CarModel $model)
	{	
		$category = $category->bySlug($categorySlug);
		$model = $model->bySlug($modelSlug);
		$categories = $category->noTypeActiveSubcategories($categorySlug);
		$types = CarModelType::whereActive('active')->whereModel_id($model->id)->orderBy('fuel')->orderBy('hp','asc')->get();
		$fuels = CarModelType::whereModel_id($model->id)->whereActive('active')->select('fuel')->distinct()->orderBy('fuel')->get();
		$breadcrumb = 'frontNoCarPartsSubcategories';
		$meta = Meta::build(null, $category);
		$item = $category;
		$total = str_replace(',', '', Cart::instance('shopping')->subtotal());
        $transport = TransportMargin::where('margin','!=',0)->get()->first()->max;
		$contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
		return view('front.partials.products.parts.nocar.selectType', compact('meta', 'breadcrumb', 'categories', 'category', 'item', 'model', 'types','contactInfo', 'total', 'transport','fuels'));
	}

}