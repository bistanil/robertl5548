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
use App\Models\ContactMessage;
use JavaScript;
use URL;

class ContactMessagesController extends Controller
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
        $messages = ContactMessage::orderBy('id', 'desc')->paginate();
        $breadcrumb='contactMessages';
        return view('admin.partials.contactMessages.main', compact('messages','breadcrumb'));
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
        $message = ContactMessage::find($id);
        $message->status = 'read';
        $message->save();
        $breadcrumb = 'contactMessages.show';
        $item = $message;
        return view('admin.partials.contactMessages.message', compact('message', 'item', 'breadcrumb'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, ContactMessage $message)
    {
        //
        $message=$message->find($id);
        if ($message->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect(route('admin-contact-messages.index'));
    }

    public function updateStatus($id, $status)
    {
        $message = ContactMessage::find($id);
        $message->second_status = $status;
        if ($message->save()) flash()->success(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusSuccessText'));
        else flash()->error(trans('admin/orders.updateStatusFlashTitle'), trans('admin/orders.updateStatusErrorText'));  
        return redirect(route('admin-contact-messages.index'));
    }
}
