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
use App\Models\County;
use App\Models\City;
use App\Http\Requests\Admin\CityRequest;
use App\Http\Requests\Admin\ExcelImportRequest;
use JavaScript;
use URL;
use Excel;

class CitiesController extends Controller
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
    public function index($id, County $county)
    {
        session()->put('adminItemsUrl',url()->full());
        $parent = $county->find($id);
        $cities = $parent->cities()->orderBy('title', 'asc')->paginate();
        $breadcrumb='cities';
        $item = $parent;
        return view('admin.partials.counties.cities.main', compact('breadcrumb', 'item', 'cities', 'parent'));
    }

    public function search(Request $request, $id, County $county)
    {
        session()->put('adminItemsUrl',url()->full());
        $item = $county->find($id);
        if (isset($request->q)) $request->session()->flash('citySearch',$request->q);
        $request->session()->keep('citySearch');         
        $search = $request->session()->get('citySearch');
        $cities = City::where('cities.county_id', '=', $id)
                        ->where(function ($query) use ($search){
                            $query->orWhere('cities.title', 'LIKE', "%$search%");
                        })
                        ->paginate(session()->get('citiesPerPage'));
        $breadcrumb='cities';
        $parent = $item;
        return view('admin.partials.counties.cities.search', compact('cities', 'breadcrumb', 'search', 'parent', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, County $county)
    {
        $parent = $county->find($id);
        $breadcrumb='cities.create';
        $item=$parent;
        return view('admin.partials.counties.cities.form', compact('breadcrumb', 'parent', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CityRequest $request, County $county, $id )
    {
        //    
        $city = new City($request->all());
        $county = $county->find($id);
        $city->county_id = $county->id;
        if ($city->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect(route('admin-cities.index', ['cityId' => $city->county->id]));
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
    public function edit(County $county, $countyId, City $city, $id)
    {
        //
        $city = $city->find($id);
        $parent = $county->find($id);
        $breadcrumb='cities.edit';
        $item=$city;
        return view('admin.partials.counties.cities.form', compact('breadcrumb','item', 'city', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CityRequest $request, County $county, $countyId, City $city, $id)
    {
        $city=$city->find($id);   
        $county = $county->find($id);
        $parent = $city->county;   
        if ($city->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        return redirect(route('admin-cities.index', ['cityId' => $city->county->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $city = City::find($id);  
        if ($city->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-cities.index', ['cityId' => $city->county->id]));
    }
}