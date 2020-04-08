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
use App\Models\Page;
use App\Models\Car;
use App\Models\CarModelGroup;
use App\Models\CarModel;
use Illuminate\Http\Request;

class CarModelsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}	

	public function index($slug, Car $car)
	{	
		$car = $car->bySlug($slug);
		$modelGroups = Car::find($car->id)->modelsGroups()->orderBy('position')->get();
		$breadcrumb = 'frontModels';
		$meta = Meta::build(null, $car);
		$item = $car;
		return view('front.partials.cars.models.main', compact('meta','breadcrumb','modelGroups','car', 'item'));
	}

	public function modelsList(Request $request)
    {
    	if ($request->carId == '') return null;
    	$groups = CarModelGroup::select('id')->whereCar_id($request->carId)->whereActive('active')->orderBy('position')->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->get();
        return view('front.partials.cars.models.sidebarModelsList', compact('models'));
    }

    public function typesPath(Request $request)
    {
    	$model = CarModel::find($request->modelId);
    	echo route('front-types', ['slug' => $model->slug]);
    }

}