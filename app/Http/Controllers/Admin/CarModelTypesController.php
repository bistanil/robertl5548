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
use App\Models\CarModel;
use App\Models\CarModelType;
use App\Http\Requests\Admin\CarModelTypeRequest;
use App\Events\CarModelTypeDelete;
use JavaScript;
use URL;

class CarModelTypesController extends Controller
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
    public function index($id, CarModel $model)
    {
        session()->put('adminItemsUrl',url()->full());
        $types = $model->find($id)->types()->sorted()->paginate(session()->get('typesPerPage'));
        $breadcrumb='carTypes';
        $item = $model->find($id);
        return view('admin.partials.cars.types.main', compact('types','breadcrumb', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $id, CarModel $model)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('typeSearch',$request->q);
        $request->session()->keep('typeSearch');         
        $search = $request->session()->get('typeSearch');
        $types = CarModelType::where('car_model_types.model_id', '=', $id)
                          ->where(function ($query) use ($search){
                                $query->orWhere('car_model_types.title', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.cc', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.kw', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.hp', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.cylinders', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.engine', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.fuel', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.body', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.axle', 'LIKE', "%$search%")
                                      ->orWhere('car_model_types.content', 'LIKE', "%$search%");
                            })                                                    
                          ->paginate(session()->get('typesPerPage'));
        $breadcrumb='carTypes';
        $item = $model->find($id);
        return view('admin.partials.cars.types.search', compact('types', 'breadcrumb', 'search', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, CarModel $model)
    {
        $item = $model->find($id);
        $breadcrumb='carTypes.create';
        return view('admin.partials.cars.types.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, CarModel $model, CarModelTypeRequest $request)
    {
        //
        $model = $model->find($id);
        $type = new CarModelType($request->all());
        $type->model_id = $model->id;
        $type->language = $model->language;
        $type->slug = str_slug($model->modelsGroup->car->title.'-'.$model->title.'-'.$type->title, '-');
        if ($type->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-model-types.index', ['modelId' => $id]));
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
    public function edit($modelId, CarModel $model, $id, CarModelType $type)
    {
        //
        $type = $type->find($id);        
        $breadcrumb='carTypes.edit';
        $item=$type;
        return view('admin.partials.cars.types.form', compact('type','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarModelTypeRequest $request, $modelId, CarModel $model, $id, CarModelType $type)
    {
        //
        $type=$type->find($id);
        $model=$type->model;
        $type->slug = str_slug($model->modelsGroup->car->title.'-'.$model->title.'-'.$type->title, '-');
        if ($type->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-car-model-types.index', ['modelId' => $modelId]));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($modelId, $parentId, $entityId)
    {
        //
        $entity = CarModelType::find($entityId);
        $positionEntity = CarModelType::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-model-types.index', ['modelId' => $modelId]));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($modelId, $parentId, $entityId)
    {
        //
        $entity = CarModelType::find($entityId);
        $positionEntity = CarModelType::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-model-types.index', ['modelId' => $modelId]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($modelId, $id, CarModelType $type)
    {
        //
        $type=$type->find($id);        
        event(new CarModelTypeDelete($type));
        if ($type->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-car-model-types.index', ['modelId' => $modelId]));
    }

    public function typesList(Request $request)
    {
        $types = CarModelType::whereIn('model_id', $request->modelIds)->get();
        return view('admin.partials.cars.types.typesList', compact('types'));
    }

    public function searchTypesList(Request $request)
    {
        if ($request->modelId == '') return null;
        $types = CarModelType::whereModel_id($request->modelId)->whereActive('active')->get();
        return view('admin.partials.cars.types.'.$request->type.'TypesList', compact('types'));
    }

    public function motorcycleTypesList(Request $request)
    {
        if ($request->modelId == '') return null;
        $types = CarModelType::whereModel_id($request->modelId)->whereActive('active')->get();
        return view('admin.partials.cars.types.'.$request->type.'TypesList', compact('types'));
    }

    public function trucksTypesList(Request $request)
    {
        if ($request->modelId == '') return null;
        $types = CarModelType::whereModel_id($request->modelId)->whereActive('active')->get();
        return view('admin.partials.cars.types.'.$request->type.'TypesList', compact('types'));
    }

    public function otherTypesList(Request $request)
    {
        if ($request->modelId == '') return null;
        $types = CarModelType::whereModel_id($request->modelId)->whereActive('active')->get();
        return view('admin.partials.cars.types.'.$request->type.'TypesList', compact('types'));
    }
}