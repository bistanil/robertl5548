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
use App\Models\Career;
use App\Models\CareerApply;
use App\Http\Requests\Admin\CareerApplyRequest;
use Excel;
use JavaScript;
use URL;

class CareerCandidatesController extends Controller
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
    public function index($slug, Career $career, Request $request)
    {
        $career = $career->bySlug($slug);
        $item = $career;
        $candidates = $career->candidates;
        $breadcrumb='careerApplies';
        $request->session()->put('careerCandidatesUrl', $request->fullUrl());
        return view('admin.partials.careers.candidates.careerCandidates', compact('candidates','breadcrumb', 'career', 'item'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $candidate = CareerApply::find($id);
        $candidate->status = 'read';
        $candidate->save();
        $breadcrumb = 'careerApplies.show';
        $item = $candidate;
        return view('admin.partials.careers.candidates.show', compact('candidate', 'item', 'breadcrumb'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        // 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CareerApply $candidate, $id, Request $request)
    {
        //
        $candidate=$candidate->find($id);
        hwImage()->destroy($candidate->docs, 'careerApply');         
        if ($candidate->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));  
        return back();
    }
}
