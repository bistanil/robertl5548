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
use App\Models\Suggestion;
use JavaScript;
use URL;

class SuggestionsMessagesController extends Controller
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
        $suggestions = Suggestion::orderBy('id', 'desc')->paginate();
        $breadcrumb='suggestions';
        return view('admin.partials.suggestions.main', compact('suggestions','breadcrumb'));
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
        $suggestion = Suggestion::find($id);
        $suggestion->status = 'read';
        $suggestion->save();
        $breadcrumb = 'suggestions.show';
        $item = $suggestion;
        return view('admin.partials.suggestions.message', compact('suggestion', 'item', 'breadcrumb'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Suggestion $suggestion)
    {
        //
        $suggestion=$suggestion->find($id);
        if ($suggestion->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));     
        return redirect(route('admin-suggestions-messages.index'));
    }
}
