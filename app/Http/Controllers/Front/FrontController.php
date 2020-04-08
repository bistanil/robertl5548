<?php namespace App\Http\Controllers\Front;

use App;
use Auth;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use URL;
use JavaScript;
use Carbon\Carbon;
use DB;
use App\Http\Libraries\Meta;
use App\Models\Page;
use App\Models\Slide;
use App\Models\Car;
use App\Models\CatalogProduct;
use App\Models\PartsCategory;
use App\Models\Catalog;
use App\Models\Career;
use App\Models\Banner;
use Illuminate\Http\Request;
use Cart;

class FrontController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

	public function index(CatalogProduct $products)
	{			
		$slides = Slide::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get();
		$catalogs = Catalog::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get();
		
		$brands = Car::whereActive('active')->whereFirstPage('yes')->orderBy('title')->get();
		$careers = Career::whereActive('active')->whereLanguage(App::getLocale())->orderBy('position')->get();
		$breadcrumb='frontHome';
		$meta = Meta::build('home');
		return view('front.partials.home.main', compact('meta','breadcrumb','slides','brands','products','catalogs', 'careers'));
		 
    }

	public function page($slug, Page $page)
	{
		$breadcrumb='frontPages';
		$page = $page->bySlug($slug);
		if ($page == null)
		{ 
			$breadcrumb='frontHome';
			$meta = Meta::build('home');
			return view('front.partials.notFound', compact('meta', 'breadcrumb'));
		}
		$meta = Meta::build(null, $page);
		$item = $page;
		return view('front.partials.pages.page', compact('page', 'meta','breadcrumb', 'item'));
	}

	public function subpage($pageSlug, $subpageSlug, Page $page)
	{
		$breadcrumb='frontSubPages';
		$parent=$page->bySlug($pageSlug);
		if ($page == null)
		{ 
			$breadcrumb='frontHome';
			$meta = Meta::build('home');
			return view('front.partials.notFound', compact('meta', 'breadcrumb'));
		}
        $page = $page->bySlug($subpageSlug);
        $item=$page;
        $meta = Meta::build(null, $page);       
        return view('front.partials.pages.page', compact('page','breadcrumb','item','parent','meta'));	
    }

}