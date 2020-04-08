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
use App\Models\CarModelType;
use App\Models\CarEngine;
use App\Http\Requests\Admin\CarEngineRequest;
use JavaScript;
use URL;

class CarTypeEnginesController extends Controller
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
    public function index($id, CarModelType $type)
    {
        session()->put('adminItemsUrl',url()->full());
        $engines = $type->find($id)->engines()->sorted()->paginate();
        $breadcrumb='carEngines';
        $item = $type->find($id);
        return view('admin.partials.cars.types.engines.main', compact('engines','breadcrumb', 'item'));
    }

    public function search(Request $request)
    {        
        session()->forget('partsCategory');
        if (isset($request->search_code)) session()->put('partEngineCodeSearch',$request->search_code);
        $search = session()->get('partEngineCodeSearch');
        $types = CarModelType::join('car_engines', function ($join) use ($search){
                                        $join->on('car_model_types.id', '=', 'car_engines.type_id')
                                             ->where('car_engines.search_code', 'LIKE', preg_replace("/[^a-zA-Z0-9]+/","", $search));
                                })
                                ->select('car_model_types.*')
                                ->distinct()
                                ->paginate(15);
        $breadcrumb = 'partSearchEngineCode';
        return view('admin.partials.cars.types.engines.search', compact('breadcrumb', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, CarModelType $type)
    {
        $item = $type->find($id);
        $breadcrumb='carEngines.create';
        return view('admin.partials.cars.types.engines.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, CarModelType $type, CarEngineRequest $request)
    {
        //
        $type = $type->find($id);
        $engine = new CarEngine($request->all());
        $engine->type_id = $type->id;
        $engine->language = $type->language;
        if ($engine->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-type-engines.index', ['typeId' => $id]));
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
    public function edit($typeId, $id, CarEngine $engine)
    {
        //
        $engine = $engine->find($id);        
        $breadcrumb='carEngines.edit';
        $item=$engine;
        return view('admin.partials.cars.types.engines.form', compact('engine','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarEngineRequest $request, $typeId, CarModelType $type, $id, CarEngine $engine)
    {
        //
        $engine=$engine->find($id);
        $type=$engine->type;
        if ($engine->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-car-type-engines.index', ['typeId' => $typeId]));
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($typeId, $parentId, $entityId)
    {
        //
        $entity = CarEngine::find($entityId);
        $positionEntity = CarEngine::find($parentId);
        $entity->moveBefore($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-type-engines.index', ['typeId' => $typeId]));
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($typeId, $parentId, $entityId)
    {
        //
        $entity = CarEngine::find($entityId);
        $positionEntity = CarEngine::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-type-engines.index', ['typeId' => $typeId]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($typeId, $id, CarEngine $engine)
    {
        //
        $engine=$engine->find($id);        
        if ($engine->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-car-type-engines.index', ['typeId' => $typeId]));
    }
}