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
use Illuminate\Http\Request;
use App\Http\Libraries\Meta;
use App\Models\Page;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Http\Requests\Front\CarSearchRequest;

class CarsController extends Controller {

	public function __construct()
	{
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}	

	public function index()
	{	
	}

	public function brands()
	{	
		$breadcrumb='frontBrands';		
		$meta = Meta::build('home');
		$brands = Car::whereActive('active')->orderBy('title')->get();
		return view('front.partials.cars.main', compact('meta','breadcrumb','brands'));
	}

	public function models($slug, Car $car)
	{
		$car = $car->bySlug($slug);
		$modelsGroups = Car::find($car->id)->modelsGroups()->get();
		$breadcrumb = 'frontModels';
		$meta = Meta::build('home');
		return view('front.partials.cars.models.main', compact('meta','breadcrumb','modelsGroups','modelImage'));
	} 

	public function types()
	{
		$breadcrumb = 'frontTypes';
		//$meta = Meta::build('home');
		return view('front.partials.cars.models.types.main', compact('meta','breadcrumb'));
	} 

	public function search(CarSearchRequest $request)
	{
		$type = CarModelType::find($request->type_id);
		session()->put('type', $type);
		return redirect(route('front-parts-categories', ['type' => $type->slug]));
	}


}