<?php

namespace App\Http\Controllers\Admin\Awb;

use Illuminate\Http\Request;

use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\AwbContactPerson;
use App\Http\Requests\Admin\AwbContactPersonRequest;

class ContactPeopleController extends Controller
{

    public function __construct(User $user)
    {
        $this->middleware('auth');              
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $persons = AwbContactPerson::all();
        $breadcrumb='contactPeople';
        return view('admin.partials.awbs.people.main', compact('persons','breadcrumb'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb='contactPeople.create';
        return view('admin.partials.awbs.people.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AwbContactPersonRequest $request)
    {
        $person = new AwbContactPerson($request->all());
        if ($person->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        return redirect(route('admin-awb-contact-people.index'));
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
    public function edit($id, AwbContactPerson $person)
    {
        $person=$person->find($id);
        $breadcrumb='contactPeople.edit';
        $item=$person;
        return view('admin.partials.awbs.people.form', compact('person','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AwbContactPersonRequest $request, $id, AwbContactPerson $person)
    {
        //
        $person=$person->find($id);
        if ($person->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));;        
        return redirect(route('admin-awb-contact-people.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, AwbContactPerson $person)
    {
        //
        $person=$person->find($id);
        if ($person->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect(route('admin-awb-contact-people.index'));
    }
}
