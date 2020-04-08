<?php namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use Session;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App;
use App\Models\Newsletter;
use Excel;
use JavaScript;
use URL;

class NewsletterController extends Controller
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
        $emails = Newsletter::orderBy('id', 'desc')->paginate(session()->get('emailsPerPage'));
        $breadcrumb='newsletter';
        return view('admin.partials.newsletter.main', compact('emails','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('newsletterSearch',$request->q);
        $request->session()->keep('newsletterSearch');         
        $search = $request->session()->get('newsletterSearch');
        $emails = Newsletter::where('news_email', 'LIKE', "%$search%")
                           ->paginate(session()->get('emailsPerPage'));
        $breadcrumb='newsletter';      
        return view('admin.partials.newsletter.search', compact('emails', 'breadcrumb', 'search'));
    }

    /**
     * Export newsletter to excel
     *
     * @param  int  $slug     
     */
    public function excelExport()
    {
        //ob_end_clean();
        //ob_start();
        $emails = Newsletter::whereActive('active')->get();
        $excel = App::make('excel');
        Excel::create(trans('admin/newsletter.newsletter'), function($excel) use ($emails) {
            $excel->sheet(trans('admin/catalogs.products'), function($sheet) use ($emails) {
                $sheet->loadView('admin.partials.newsletter.excel')
                      ->with('emails', $emails);
            })->download('xlsx');
        });
        ob_flush();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Newsletter $email, $id, Request $request)
    {
        //
        $email=$email->find($id);        
        if ($email->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));   
        return redirect(route('admin-newsletter.index'));
    }

}