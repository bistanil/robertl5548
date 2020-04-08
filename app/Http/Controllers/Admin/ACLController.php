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
use App\Models\AccessControlSection;
use App\Http\Requests\Admin\ACLRequest;
use JavaScript;
use URL;

class ACLController extends Controller
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
        $groups = AccessControlSection::select('group', 'label', 'parent', 'show_actions')->distinct()->orderBy('group')->get();
        $breadcrumb='admin';
        return view('admin.partials.acl.main', compact('groups','breadcrumb'));
    }

     public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('aclSearch',$request->q);
        $request->session()->keep('aclSearch');         
        $search = $request->session()->get('aclSearch');
        $groups = AccessControlSection::select('group', 'label', 'parent', 'show_actions')->distinct()
                          ->where('access_control_sections.group', 'LIKE', "%$search%")
                          ->orWhere('access_control_sections.label', 'LIKE', "%$search%")
                          ->orWhere('access_control_sections.parent', 'LIKE', "%$search%")
                          ->orderBy('group')
                          ->get();
        $breadcrumb='admin';
        return view('admin.partials.acl.search', compact('groups', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='admin';
        $groups = AccessControlSection::select('group', 'label', 'parent', 'show_actions')->distinct()->orderBy('group')->get();
        return view('admin.partials.acl.form', compact('breadcrumb', 'groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ACLRequest $request)
    {
        $group = new AccessControlSection($request->all());
        if ($group->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect(route('hw-acl.index'));
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
    public function edit($groupTitle, AccessControlSection $group)
    {
        $group=$group->whereGroup($groupTitle)->get()->first();
        $groups = AccessControlSection::select('group', 'label', 'parent', 'show_actions')->distinct()->orderBy('group')->get();
        $breadcrumb='admin';
        return view('admin.partials.acl.form', compact('group','breadcrumb','groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ACLRequest $request, $groupTitle, AccessControlSection $group)
    {    
        AccessControlSection::whereParent($groupTitle)->update(['parent' => $request->group]);
        if (AccessControlSection::whereGroup($groupTitle)->update(['group' => $request->group, 'label' => $request->label, 'parent' => $request->parent, 'show_actions' => $request->show_actions]) > 0) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect(route('hw-acl.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($groupTitle)
    {        
        if (AccessControlSection::whereGroup($groupTitle)->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect(route('hw-acl.index'));
    }
}
