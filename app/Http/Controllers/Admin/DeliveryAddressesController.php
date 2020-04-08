<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\Client;
use App\Models\DeliveryAddress;
use App\Models\County;
use App\Models\City;
use App\Http\Requests\Admin\DeliveryAddressRequest;
use JavaScript;
use URL;

class DeliveryAddressesController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');
        JavaScript::put(['baseUrl' => URL::to('/')]);              
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug, Client $client)
    {
        session()->put('adminItemsUrl',url()->full());
        $addresses = $client->bySlug($slug)->deliveryAddresses()->paginate();
        $breadcrumb='deliveryAddresses';
        $item = $client->bySlug($slug);
        return view('admin.partials.clients.addresses.main', compact('addresses','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Client $client)
    {
        $item = $client->bySlug($slug);
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $cities = [];
        $breadcrumb='deliveryAddress.create';
        return view('admin.partials.clients.addresses.form', compact('breadcrumb', 'item', 'cities', 'counties'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($slug, Client $client, DeliveryAddressRequest $request)
    {
        //
        $county = County::find($request->county_id);
        $city = City::find($request->city_id);
        $client = $client->bySlug($slug);
        $address = new DeliveryAddress($request->all());
        $address->county = $county->title;
        $address->city = $city->title;
        $address->client_id = $client->id;
        if ($address->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-client-delivery-addresses.index', ['slug' => $slug]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug, Client $client, $id, DeliveryAddress $address)
    {
        //
        $address = $address->find($id);
        $counties = County::whereActive('active')->orderBy('title','ASC')->get();
        $county = County::whereTitle($address->county)->get()->first();
        $city = City::whereTitle($address->city)->get()->first();
        if($county != null) $cities = $county->cities()->orderBy('title','ASC')->get();
        else $cities = [];
        $breadcrumb='deliveryAddress.edit';
        $item=$address;
        return view('admin.partials.clients.addresses.form', compact('address','breadcrumb','item', 'counties', 'cities', 'county', 'city'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DeliveryAddressRequest $request, $slug, Client $client, $id, DeliveryAddress $address)
    {
        //
        $county = County::find($request->county_id);
        $city = City::find($request->city_id);
        $address=$address->find($id);        
        $address->county = $county->title;
        $address->city = $city->title;        
        if ($address->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));       
        return redirect(route('admin-client-delivery-addresses.index', ['slug' => $address->client->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id, DeliveryAddress $address)
    {
        //
        $address=$address->find($id);        
        if ($address->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-client-delivery-addresses.index', ['slug' => $address->client->slug]));
    }

    public function getCities(Request $request)
    {
        $county = County::find($request->countyId);
        $cities = $county->cities()->orderBy('title','ASC')->get();
        return view('admin.partials.clients.addresses.citiesList', compact('cities'));
    }
}