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
use App\Models\Client;
use App\Models\ClientCar;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Models\CarModelGroup;
use App\Http\Requests\Front\ClientCarRequest;

class ClientCarsController extends Controller {

	public function __construct()
	{		
		JavaScript::put(['baseUrl' => URL::to('/')]);		
	}

    public function index()
	{	
		$client = Auth::guard('client')->user();
		$cars = ClientCar::whereClient_id($client->id)->paginate();
		$meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientCars';        
        return view('front.partials.clients.cars.main', compact('meta', 'breadcrumb', 'client', 'cars'));
	}

	public function create()
	{
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientCarCreate';
		$cars = Car::whereActive('active')->orderBy('title', 'ASC')->sorted()->get();
        if (session()->has('type')) 
        {
            $groups = CarModelGroup::select('id')->where('car_id', session()->get('type')->model->modelsGroup->car->id)->whereActive('active')->get();
            $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->sorted()->get();                
        } else $models = [];            
        if (session()->has('type')) {
            $fuels = CarModelType::select('fuel')->distinct()->whereModel_id(session()->get('type')->model_id)->whereActive('active')->get();
        } else $fuels = [];
        if (session()->has('type')) $types = CarModelType::whereModel_id(session()->get('type')->model_id)->whereActive('active')->sorted()->get();
        else $types = [];
		return view('front.partials.clients.cars.form', compact('meta', 'breadcrumb', 'cars', 'models', 'types','fuels'));
	}

	public function store(ClientCarRequest $request, ClientCar $car)
	{
		if (ClientCar::whereClient_id(Auth::guard('client')->user()->id)->whereType_id($request->type_id)->get()->count() == 0)
		{
			$car = new ClientCar($request->all());
			$car->client_id = Auth::guard('client')->user()->id;
			if ($car->save()) frontFlash()->success(trans('front/common.addFlashTitle'), trans('front/common.addSuccessText'));
			else frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/common.addErrorText'));
			return redirect(route('front-client-cars'));
		}
		frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/clients.carExistsText'));
		return redirect(route('front-client-cars'));
	}

	public function save()
	{
		if (ClientCar::whereClient_id(Auth::guard('client')->user()->id)->whereType_id(session()->get('type')->id)->get()->count() == 0)
		{
			$car = new ClientCar();
			$car->client_id = Auth::guard('client')->user()->id;
			$car->type_id = session()->get('type')->id;
			if ($car->save()) frontFlash()->success(trans('front/common.addFlashTitle'), trans('front/common.addSuccessText'));
			else frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/common.addErrorText'));
			return redirect(route('front-client-cars'));
		}
		frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/clients.carExistsText'));
		return redirect(route('front-client-cars'));
	}
	
	public function reset()
	{
		if(session()->has('type')) {
			session()->forget('type');
		}

		return redirect(route('front-brands'));
	}

	public function edit($id, ClientCar $car)
	{
		$car = $car->find($id);
		$cars = Car::whereActive('active')->whereLanguage(App::getLocale())->get();		
		$modelGroups = CarModelGroup::whereCar_id($car->type->model->modelsGroup->car_id)->whereActive('active')->select('id')->get()->toArray();
		$models = CarModel::whereIn('model_group_id', $modelGroups)->whereActive('active')->get();
		$types = CarModelType::whereModel_id($car->type->model->id)->whereActive('active')->get();		
		$fuels = CarModelType::select('fuel')->distinct()->whereModel_id($car->type_id)->whereActive('active')->get();
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientCarEdit';
		$item = $car;
		return view('front.partials.clients.cars.editForm', compact('meta', 'breadcrumb', 'cars', 'models', 'types', 'car', 'fuels', 'item'));
	}

	public function update(ClientCar $car, ClientCarRequest $request)
	{
		$car = $car->find($request->id);
		if ($car->update($request->except('id'))) frontFlash()->success(trans('front/common.editFlashTitle'), trans('front/common.editSuccessText'));
        else frontFlash()->error(trans('front/common.editFlashTitle'), trans('front/common.editErrorText'));
        return redirect(route('front-client-cars'));
	}

	public function destroy($id, ClientCar $car)
	{
		$car = $car->find($id);
		if ($car->delete()) frontFlash()->success(trans('front/common.deleteFlashTitle'), trans('front/common.deleteSuccessText'));
		else frontFlash()->error(trans('front/common.deleteFlashTitle'), trans('front/common.deleteErrorText'));
		return redirect(route('front-client-cars'));
	}

}