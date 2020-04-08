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
use App\Models\Order;
use App\Models\OrderNote;
use App\Http\Requests\Admin\OrderNoteRequest;
use JavaScript;
use URL;

class OrderNotesController extends Controller
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
    public function index($id, Order $order)
    {
        $notes = $order->find($id)->notes()->paginate();
        $breadcrumb='orderNotes';
        $item = $order->find($id);
        return view('admin.partials.orders.notes.main', compact('notes','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, Order $order)
    {
        $order = $order->find($id);
        $breadcrumb='orderNotes.create';
        $item = $order;
        return view('admin.partials.orders.notes.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, Order $order, OrderNoteRequest $request)
    {
        //
        $order = $order->find($id);
        $note = new OrderNote($request->all());
        $note->order_id = $order->id;
        $note->user_id = Auth::user()->id;
        if ($note->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        //return redirect(route('admin-order-notes.index', ['orderId' => $id]));
        return redirect(route('admin-orders.show', ['orderId' => $id]));
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
    public function edit($orderId, Order $order, $id, OrderNote $note)
    {
        //
        $note = $note->find($id);
        $breadcrumb='orderNote.edit';
        $item=$note;
        return view('admin.partials.orders.notes.form', compact('note','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OrderNoteRequest $request, $orderId, Order $irder, $noteId, OrderNote $note)
    {
        //
        $note=$note->find($noteId);        
        if ($note->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-order-notes.index', ['id' => $note->order->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($orderId, $noteId, OrderNote $note)
    {
        //
        $note=$note->find($noteId);        
        if ($note->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        //if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-order-notes.index', ['id' => $note->order->id]));
    }
}