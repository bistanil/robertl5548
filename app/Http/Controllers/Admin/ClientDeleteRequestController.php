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
use App\Models\ClientDeleteRequest;
use JavaScript;
use URL;

class ClientDeleteRequestController extends Controller
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
        $messages = ClientDeleteRequest::orderBy('id', 'desc')->paginate();
        $breadcrumb='clientDeleteRequests';
        return view('admin.partials.clientDeleteRequests.main', compact('messages','breadcrumb'));
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
        $message = ClientDeleteRequest::find($id);
        $message->status = 'read';
        $message->save();
        $breadcrumb = 'clientDeleteRequests.show';
        $item = $message;
        return view('admin.partials.clientDeleteRequests.message', compact('message', 'item', 'breadcrumb'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, ClientDeleteRequest $message)
    {
        //
        $message=$message->find($id);
        if ($message->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect(route('admin-client-delete-request.index'));
    }

    public function updateStatus($id, $status)
    {
        $message = ClientDeleteRequest::find($id);
        $message->second_status = $status;
        if ($message->save()) flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        else flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText'));  
        return redirect(route('admin-client-delete-request.index'));
    }
}
