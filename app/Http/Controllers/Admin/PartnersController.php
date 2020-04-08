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
use App\Models\Partner;
use App\Http\Requests\Admin\PartnerRequest;
use JavaScript;
use URL;

class PartnersController extends Controller
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
        $partners = Partner::sorted()->paginate(session()->get('partnersPerPage'));
        $breadcrumb='partners';
        return view('admin.partials.partners.main', compact('partners','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='partner.create';
        return view('admin.partials.partners.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartnerRequest $request)
    {
        $partner = new Partner($request->all());
        $partner->image = hwImage()->heighten($request, 'partner');
        if ($partner->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect('admin-partners');
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
    public function edit($id, Partner $partner)
    {
        $partner=$partner->find($id);
        $breadcrumb='partner.edit';
        $item=$partner;
        return view('admin.partials.partners.form', compact('partner','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PartnerRequest $request, $id, Partner $partner)
    {
        //
        $partner=$partner->find($id);
        if ($request->hasFile('image'))
        {
            hwImage()->destroy($partner->image, 'partner');
            $partner->image = hwImage()->heighten($request, 'partner');
        }
        if ($partner->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect('admin-partners');
    }

    /**
     * Move the partner up one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortUp($parentId,$entityId)
    {
        //
        $entity = Partner::find($entityId);
        $positionEntity = Partner::find($parentId);
        $entity->moveBefore($positionEntity);
        return redirect('admin-partners');
    }

    /**
     * Move the partner down one position.
     *
     * @param  int  $entityid
     * @param  int  $parentid
     */
    public function sortDown($parentId,$entityId)
    {
        //
        $entity = Partner::find($entityId);
        $positionEntity = Partner::find($parentId);
        $entity->moveAfter($positionEntity);
        return redirect('admin-partners');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Partner $partner)
    {
        //
        $partner=$partner->find($id);
        hwImage()->destroy($partner->image, 'partner');
        if ($partner->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect('admin-partners');
    }
}
