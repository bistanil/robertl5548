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
use App\Models\Client;
use App\Models\Order;
use App\Http\Requests\Admin\ClientRequest;
use App\Events\ClientDelete;
use JavaScript;
use URL;

class ClientsController extends Controller
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
        $clients = Client::orderBy('id', 'desc')->paginate(session()->get('clientsPerPage'));
        $breadcrumb = 'clients';
        return view('admin.partials.clients.main', compact('clients','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        session()->put('adminItemsUrl',url()->full());
        if (isset($request->q)) $request->session()->flash('clientSearch',$request->q);
        $request->session()->keep('clientSearch');         
        $search = $request->session()->get('clientSearch');
        $clients = Client::where('clients.name', 'LIKE', "%$search%")
                          ->orWhere('clients.email', 'LIKE', "%$search%")
                          ->orWhere('clients.phone', 'LIKE', "%$search%")
                          ->paginate(session()->get('clientsPerPage'));
        $breadcrumb = 'clients';
        return view('admin.partials.clients.search', compact('clients', 'breadcrumb', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb = 'client.create';
        return view('admin.partials.clients.form', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        //
        $client = new Client($request->all());
        $client->password = bcrypt($client->password);
        $client->slug=str_slug($client->name.'-'.$client->email.'-'.$client->phone, "-");
        $client->origin = 'admin';
        if ($client->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect('admin-clients');
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
    public function edit($slug)
    {
        //
        $client = Client::whereSlug($slug)->get()->first();
        $breadcrumb = 'client.edit';
        $item = $client;
        return view('admin.partials.clients.form', compact('client','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, $slug)
    {
        //
        $client = Client::whereSlug($slug)->get()->first();
        $client->slug = str_slug($request->name.'-'.$request->email.'-'.$request->phone, "-");
        $client->save();
        if (!empty($request->password)) {
            if ($request->password === $request->password_confirmation) {
                $client->password = bcrypt($request->password);
                $client->save();                
            }
        }
        if ($client->update($request->except('password','password_confirmation'))) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect('admin-clients');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        //
        $client=Client::whereSlug($slug)->get()->first();        
        event(new ClientDelete($client));
        if ($client->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect('admin-clients');
    }

    public function companiesAddressesLists(Request $request)
    {
        if ($request->clientId>0)
        {
            $client = Client::find($request->clientId);
            return view('admin.partials.clients.lists', compact('client'));
        }
    }

    public function discount(Request $request)
    {
        if ($request->clientId>0)
        {
            $client = Client::find($request->clientId);
            return view('admin.partials.clients.discount', compact('client'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function orders($slug)
    {
        session()->put('adminItemsUrl',url()->full());
        $client = Client::whereSlug($slug)->get()->first();
        $orders = Order::where('client_id', '=', $client->id)->orderBy('id','desc')->paginate();
        $breadcrumb = 'clientOrders';
        $item = $client;
        return view('admin.partials.orders.main', compact('orders','breadcrumb', 'client', 'item'));
    }

    public function getClient(Request $request)
    {
        $client = Client::find($request->clientId);
        $clientArr = [];
        $clientArr['name'] = $client->name;
        $clientArr['email'] = $client->email;
        $clientArr['phone'] = $client->phone;
        echo json_encode($clientArr);
    }
}
