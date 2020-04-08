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
use App\Models\CarModelType;
use App\Http\Requests\Admin\CarRequest;
use App\Events\CarDelete;
use App\Http\Requests\Admin\CarSearchRequest;
use JavaScript;
use URL;

class CarsController extends Controller
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
    public function index()
    {
        session()->put('adminItemsUrl',url()->full());
        $cars = Car::sorted()->paginate(session()->get('carsPerPage'));
        $breadcrumb='cars';
        return view('admin.partials.cars.main', compact('cars','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('carSearch',$request->q);
        $request->session()->keep('carSearch');         
        $search = $request->session()->get('carSearch');
        $cars = Car::where('cars.title', 'LIKE', "%$search%")
                          ->orWhere('cars.content', 'LIKE', "%$search%")                          
                          ->paginate(session()->get('carsPerPage'));
        $breadcrumb='cars';
        return view('admin.partials.cars.search', compact('cars', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='car.create';
        return view('admin.partials.cars.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CarRequest $request)
    {
        //
        $car = new Car($request->all());
        $car->slug=str_slug($car->title, "-");
        $car->image = hwImage()->heighten($request, 'car');
        if ($car->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-cars');
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
    public function edit($id, Car $car)
    {
        //
        $car = $car->find($id);
        $breadcrumb='car.edit';
        $item=$car;
        return view('admin.partials.cars.form', compact('car','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarRequest $request, $id, Car $car)
    {
        //
        $car=$car->find($id);
        $car->slug=str_slug($request->title, "-");
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($car->image, 'car');
            $car->image = hwImage()->heighten($request, 'car');            
        }
        if ($car->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-cars');
    }

    /**
     * Move up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Car::find($entityId);
        $positionEntity = Car::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-cars');
    }

    /**
     * Move down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Car::find($entityId);
        $positionEntity = Car::find($parentId);
        $entity->moveAfter($positionEntity);
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-cars');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Car $car)
    {
        //
        $car=$car->find($id);        
        event(new CarDelete($car));
        hwImage()->destroy($car->image, 'car');
        if ($car->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-cars');
    }

    public function carSearch(CarSearchRequest $request)
    {
        $type = CarModelType::find($request->type_id);
        session()->put('admin-type', $type);
        return redirect(route('admin-parts-categories.searchCategories', ['type' => $type->slug]));
    }

    public function reset()
    {
        if(session()->has('admin-type')) {
            session()->forget('admin-type');
        }
        return redirect(route('admin'));
    }
}