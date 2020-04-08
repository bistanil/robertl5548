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
use App\Models\Car;
use App\Models\CarModelGroup;
use App\Http\Requests\Admin\CarModelsGroupRequest;
use App\Events\CarModelsGroupDelete;
use JavaScript;
use URL;

class CarModelsGroupsController extends Controller
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
    public function index($id, Car $car)
    {
        session()->put('adminItemsUrl',url()->full());
        $modelsGroups = $car->find($id)->modelsGroups()->sorted()->paginate(session()->get('modelGroupsPerPage'));
        $breadcrumb='carModelsGroups';
        $item = $car->find($id);
        return view('admin.partials.cars.models.groups.main', compact('modelsGroups','breadcrumb', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $id, Car $car)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('modelGroupSearch',$request->q);
        $request->session()->keep('modelGroupSearch');         
        $search = $request->session()->get('modelGroupSearch');
        $modelsGroups = CarModelGroup::where('car_id',  '=', $id)
                                     ->where(function ($query) use ($search){
                                            $query->orWhere('car_model_groups.title', 'LIKE', "%$search%");                                                  
                                        })                                                                   
                                     ->paginate(session()->get('modelGroupsPerPage'));
        $breadcrumb='carModelsGroups';
        $item = $car->find($id);
        return view('admin.partials.cars/models/groups.search', compact('modelsGroups', 'breadcrumb', 'search', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, Car $car)
    {
        $item = $car->find($id);
        $breadcrumb='carModelsGroups.create';
        return view('admin.partials.cars.models.groups.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, Car $car, CarModelsGroupRequest $request)
    {
        //
        $car = $car->find($id);
        $modelsGroup = new CarModelGroup($request->all());
        $modelsGroup->car_id = $car->id;
        $modelsGroup->language = $car->language;
        $modelsGroup->image = hwImage()->heighten($request, 'carModelsGroup');
        if ($modelsGroup->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models-groups.index', ['carId' => $id]));
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
    public function edit($carId, Car $car, $id, CarModelGroup $modelsGroup)
    {
        //
        $modelsGroup = $modelsGroup->find($id);
        $breadcrumb='carModelsGroups.edit';
        $item=$modelsGroup;
        return view('admin.partials.cars.models.groups.form', compact('modelsGroup','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarModelsGroupRequest $request, $carId, Car $car, $id, CarModelGroup $modelsGroup)
    {
        //
        $modelsGroup=$modelsGroup->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($modelsGroup->image, 'carModelsGroup');
            $modelsGroup->image = hwImage()->heighten($request, 'carModelsGroup');            
        }
        if ($modelsGroup->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-car-models-groups.index', ['carId' => $carId]));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($carId, $parentId, $entityId)
    {
        //
        $entity = CarModelGroup::find($entityId);
        $positionEntity = CarModelGroup::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models-groups.index', ['carId' => $carId]));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($carId, $parentId, $entityId)
    {
        //
        $entity = CarModelGroup::find($entityId);
        $positionEntity = CarModelGroup::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models-groups.index', ['carId' => $carId]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($carId, $id, CarModelGroup $modelsGroup)
    {
        //
        $modelsGroup=$modelsGroup->find($id);        
        event(new CarModelsGroupDelete($modelsGroup));
        hwImage()->destroy($modelsGroup->image, 'carModelsGroup');
        if ($modelsGroup->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-car-models-groups.index', ['carId' => $carId]));
    }
}