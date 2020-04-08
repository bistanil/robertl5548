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
use App\Models\CarModel;
use App\Models\CarModelGroup;
use App\Http\Requests\Admin\CarModelRequest;
use App\Events\CarModelDelete;
use JavaScript;
use URL;

class CarModelsController extends Controller
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
    public function index($id, CarModelGroup $modelsGroup)
    {
        session()->put('adminItemsUrl',url()->full());
        $models = $modelsGroup->find($id)->models()->sorted()->paginate(session()->get('modelsPerPage'));
        $breadcrumb='carModels';
        $item = $modelsGroup->find($id);
        return view('admin.partials.cars.models.main', compact('models','breadcrumb', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $id, CarModelGroup $modelsGroup)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('modelSearch',$request->q);
        $request->session()->keep('modelSearch');         
        $search = $request->session()->get('modelSearch');
        $models = CarModel::where('car_models.model_group_id', '=', $id)
                          ->where(function ($query) use ($search){
                                $query->orWhere('car_models.title', 'LIKE', "%$search%")
                                      ->orWhere('car_models.content', 'LIKE', "%$search%");
                            })                                                    
                          ->paginate(session()->get('modelsPerPage'));
        $breadcrumb='carModels';
        $item = $modelsGroup->find($id);
        return view('admin.partials.cars/models.search', compact('models', 'breadcrumb', 'search', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, CarModelGroup $modelsGroup)
    {
        $item = $modelsGroup->find($id);
        $breadcrumb='carModels.create';
        return view('admin.partials.cars.models.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, CarModelGroup $modelsGroup, CarModelRequest $request)
    {
        //
        $modelsGroup = $modelsGroup->find($id);
        $model = new CarModel($request->all());
        $model->model_group_id = $modelsGroup->id;
        $model->language = $modelsGroup->language;
        $model->image = hwImage()->heighten($request, 'carModel');
        if ($model->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models.index', ['groupId' => $id]));
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
    public function edit($groupId, CarModelGroup $modelsGroup, $id, CarModel $model)
    {
        //
        $model = $model->find($id);        
        $breadcrumb='carModels.edit';
        $item=$model;
        return view('admin.partials.cars.models.form', compact('model','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarModelRequest $request, $groupId, CarModelGroup $modelsGroup, $id, CarModel $model)
    {
        //
        $model=$model->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($model->image, 'carModel');
            $model->image = hwImage()->heighten($request, 'carModel');            
        }
        if ($model->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-car-models.index', ['groupId' => $groupId]));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($groupId, $parentId, $entityId)
    {
        //
        $entity = CarModel::find($entityId);
        $positionEntity = CarModel::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models.index', ['groupId' => $groupId]));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($groupId, $parentId, $entityId)
    {
        //
        $entity = CarModel::find($entityId);
        $positionEntity = CarModel::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-models.index', ['groupId' => $groupId]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($groupId, $id, CarModel $model)
    {
        //
        $model=$model->find($id);        
        event(new CarModelDelete($model));
        hwImage()->destroy($model->image, 'carModel');
        if ($model->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-car-models.index', ['groupId' => $groupId]));
    }

    public function modelsList(Request $request)
    {
        $groups = CarModelGroup::select('id')->whereIn('car_id', $request->carIds)->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->get();
        return view('admin.partials.cars.models.modelsList', compact('models'));
    }

    public function searchModelsList(Request $request)
    {
        if ($request->carId == '') return null;
        $groups = CarModelGroup::select('id')->whereCar_id($request->carId)->whereActive('active')->orderBy('title')->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->get();
        return view('admin.partials.cars.models.'.$request->type.'ModelsList', compact('models'));
    }

    public function motorcycleModelsList(Request $request)
    {
        if ($request->carId == '') return null;
        $groups = CarModelGroup::select('id')->whereCar_id($request->carId)->whereActive('active')->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->orderBy('title')->get();
        return view('front.partials.cars.models.'.$request->type.'ModelsList', compact('models'));
    }

    public function truckModelsList(Request $request)
    {
        if ($request->carId == '') return null;
        $groups = CarModelGroup::select('id')->whereCar_id($request->carId)->whereActive('active')->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->orderBy('title')->get();
        return view('front.partials.cars.models.'.$request->type.'ModelsList', compact('models'));
    }

    public function otherModelsList(Request $request)
    {
        if ($request->carId == '') return null;
        $groups = CarModelGroup::select('id')->whereCar_id($request->carId)->whereActive('active')->get()->toArray();
        $models = CarModel::whereIn('model_group_id', $groups)->whereActive('active')->orderBy('title')->get();
        return view('front.partials.cars.models.'.$request->type.'ModelsList', compact('models'));
    }
}