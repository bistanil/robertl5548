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
use App\Models\CarModelType;
use Illuminate\Http\Request;

class CarModelTypesController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}	

	public function index($slug, CarModel $model)
	{	
		$model = $model->bySlug($slug);
		$types = CarModelType::whereActive('active')->whereModel_id($model->id)->orderBy('fuel')->orderBy('hp','asc')->get();
        $fuels = CarModelType::whereModel_id($model->id)->whereActive('active')->select('fuel')->distinct()->orderBy('fuel')->get();
		$breadcrumb = 'frontTypes';
		$meta = Meta::build(null, $model);
		$item = $model;
		return view('front.partials.cars.models.types.main', compact('meta','breadcrumb', 'model', 'types', 'item','fuels'));
	}

	public function typesList(Request $request)
    {
    	if ($request->modelId == '') return null;
    	$types = CarModelType::whereModel_id($request->modelId)->whereFuel($request->fuel)->whereActive('active')->get();
        return view('front.partials.cars.models.types.sidebarTypesList', compact('types'));
    }

    public function fuelList(Request $request)
    {
        if ($request->modelId == '') return null;
        $fuels = CarModelType::whereModel_id($request->modelId)->whereActive('active')->select('fuel')->distinct()->get();
        return view('front.partials.cars.models.types.sidebarFuelList', compact('fuels'));
    }

    public function search(Request $request)
    {         
        session()->forget('partsCategory');
        if (isset($request->search_engine_code)) session()->put('partEngineCodeSearch',$request->search_engine_code);
        $search_engine_code = session()->get('partEngineCodeSearch');
        $types = CarModelType::join('car_engines', function ($join) use ($search_engine_code){
                                        $join->on('car_model_types.id', '=', 'car_engines.type_id')
                                             ->where('car_engines.search_code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","", $search_engine_code));
                                })
                                ->select('car_model_types.*')
                                ->distinct()
                                ->get();
        $breadcrumb = 'frontPartSearchByCode';
        $meta = Meta::build('Search');
        return view('front.partials.cars.models.types.search', compact('meta','breadcrumb', 'types'));
    }

}