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
use App\Models\ProductPrice;
use App\Models\Supplier;
use App\Http\Requests\Admin\PricesExcelImportRequest;
use Excel;
use Artisan;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Mail;
use App\Jobs\ImportPriceListJob;
use JavaScript;
use URL;


class PriceListsController extends Controller
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
        $lists = ProductPrice::select('supplier_id')->distinct()->get();
        $breadcrumb='priceLists';
        return view('admin.partials.priceLists.main', compact('lists','breadcrumb'));
    }

    /**
     * Show import form
     *     
     * @return \Illuminate\Http\Response
     */
    public function import()
    {
        //
        $breadcrumb='priceLists.import';
        $suppliers = Supplier::whereActive('active')->orderBy('title')->get();         
        return view('admin.partials.priceLists.importForm', compact('breadcrumb', 'suppliers'));
    }
   
    /**
     * Import products from Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function store( PricesExcelImportRequest $request)
    {
        //dd($request);
        $request->file('excel')->move('public/public/public_html/public/files/import/', 'pricesImport.xlsx');        
        //$process = new Process(Artisan::call('import-price-list', array('email' => Auth::user()->email, 'source' => $request->source)));
        //$process = new Process(exec('php artisan import-price-list '.$request->source.' '.Auth::user()->email));
        //$process->start();
        chdir(base_path());
        exec('bash -c "exec nohup setsid php artisan import-price-list '.$request->supplier_id.' '.Auth::user()->email.' > /dev/null 2>&1 &"');
        //$this->dispatch(new ImportPriceListJob($request->source, Auth::user()->email));
        flash()->success(trans('admin/common.importFlashTitle'), trans('admin/common.importFlashContent'));
        return redirect(route('admin-price-lists.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($supplierId)
    {
        //        
        if (ProductPrice::whereSupplier_id($supplierId)->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));   
        return redirect(route('admin-price-lists.index'));
    }

}