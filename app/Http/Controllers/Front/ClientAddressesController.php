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
use App\Models\Client;
use App\Models\DeliveryAddress;
use App\Models\County;
use App\Models\City;
use App\Http\Requests\Front\ClientAddressRequest;
use App\Http\Requests;

class ClientAddressesController extends Controller {

	public function __construct()
    {       
       JavaScript::put(['baseUrl' => URL::to('/')]); 
    }

    public function index()
	{	
		$client = Auth::guard('client')->user();
		$addresses = DeliveryAddress::whereClient_id($client->id)->paginate();
		$meta = Meta::build('ClientAccount');
        $breadcrumb = 'frontClientDeliveryAddresses';        
        return view('front.partials.clients.deliveryAddresses.main', compact('meta', 'breadcrumb', 'client', 'addresses'));
	}

	public function create()
	{
		$meta = Meta::build('ClientAccount');
		$counties = County::whereActive('active')->orderBy('title','ASC')->get();
		$cities = [];
		$breadcrumb = 'frontClientDeliveryAddressCreate';
		return view('front.partials.clients.deliveryAddresses.form', compact('meta', 'breadcrumb', 'counties', 'cities'));
	}

	public function store(ClientAddressRequest $request, DeliveryAddress $address)
	{
		$county = County::find($request->county_id);
		$city = City::find($request->city_id);
		$address = new DeliveryAddress($request->all());
		$address->county = $county->title;
		$address->city = $city->title;
		$address->client_id = Auth::guard('client')->user()->id;
		if ($address->save()) frontFlash()->success(trans('front/common.addFlashTitle'), trans('front/common.addSuccessText'));
		else frontFlash()->error(trans('front/common.addFlashTitle'), trans('front/common.addErrorText'));
		return redirect(route('front-client-addresses'));
	}

	public function edit($id, DeliveryAddress $address)
	{
		$address = $address->find($id);
		$meta = Meta::build('ClientAccount');
		$breadcrumb = 'frontClientDeliveryAddressEdit';
		$item = $address;
		$counties = County::whereActive('active')->orderBy('title','ASC')->get();
		$county = County::whereTitle($address->county)->get()->first();
		$city = City::whereTitle($address->city)->get()->first();
		if($county != null) $cities = $county->cities()->orderBy('title','ASC')->get();
		else $cities = [];
		return view('front.partials.clients.deliveryAddresses.form', compact('meta', 'breadcrumb', 'address', 'item', 'counties', 'cities', 'county', 'city'));
	}

	public function update(DeliveryAddress $address, ClientAddressRequest $request)
	{
		$county = County::find($request->county_id);
		$city = City::find($request->city_id);
		$address = $address->find($request->id);
		$address->county = $county->title;
		$address->city = $city->title;
		if ($address->update($request->except('id'))) frontFlash()->success(trans('front/common.editFlashTitle'), trans('front/common.editSuccessText'));
        else frontFlash()->error(trans('front/common.editFlashTitle'), trans('front/common.editErrorText'));
        return redirect(route('front-client-addresses'));
	}

	public function destroy($id, DeliveryAddress $address)
	{
		$address = $address->find($id);
		if ($address->delete()) frontFlash()->success(trans('front/common.deleteFlashTitle'), trans('front/common.deleteSuccessText'));
		else frontFlash()->error(trans('front/common.deleteFlashTitle'), trans('front/common.deleteErrorText'));
		return redirect(route('front-client-addresses'));
	}

	public function getCities(Request $request)
	{
		$county = County::find($request->countyId);
		$cities = $county->cities()->orderBy('title','ASC')->get();
		return view('front.partials.clients.deliveryAddresses.citiesList', compact('cities'));
	}

}