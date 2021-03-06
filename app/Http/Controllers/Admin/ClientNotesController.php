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
use App\Models\ClientNote;
use App\Http\Requests\Admin\ClientNoteRequest;
use JavaScript;
use URL;

class ClientNotesController extends Controller
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
    public function index($slug, Client $client)
    {
        session()->put('adminItemsUrl',url()->full());
        $notes = $client->bySlug($slug)->notes()->paginate();
        $breadcrumb='clientNotes';
        $item = $client->bySlug($slug);
        return view('admin.partials.clients.notes.main', compact('notes','breadcrumb', 'item'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Client $client)
    {
        $item = $client->bySlug($slug);
        $breadcrumb='clientNote.create';
        return view('admin.partials.clients.notes.form', compact('breadcrumb', 'item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($slug, Client $client, ClientNoteRequest $request)
    {
        //
        $client = $client->bySlug($slug);
        $note = new ClientNote($request->all());
        $note->client_id = $client->id;
        $note->user_id = Auth::user()->id;
        if ($note->save()) flash()->success(trans('admin/common.saveFlashTitle'), trans('admin/common.saveSuccessText'));
        else flash()->error(trans('admin/common.saveFlashTitle'), trans('admin/common.saveErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));
        return redirect(route('admin-client-notes.index', ['slug' => $slug]));
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
    public function edit($slug, Client $client, $id, ClientNote $note)
    {
        //
        $note = $note->find($id);
        $breadcrumb='clientNote.edit';
        $item=$note;
        return view('admin.partials.clients.notes.form', compact('note','breadcrumb','item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientNoteRequest $request, $slug, Client $client, $id, ClientNote $note)
    {
        //
        $note=$note->find($id);        
        if ($note->update($request->all())) flash()->success(trans('admin/common.updateFlashTitle'), trans('admin/common.updateSuccessText'));
        else flash()->error(trans('admin/common.updateFlashTitle'), trans('admin/common.updateErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));        
        return redirect(route('admin-client-notes.index', ['slug' => $note->client->slug]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $id, ClientNote $note)
    {
        //
        $note=$note->find($id);        
        if ($note->delete()) flash()->success(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteSuccessText'));
        else flash()->error(trans('admin/common.deleteFlashTitle'), trans('admin/common.deleteErrorText'));
        if (session()->has('adminItemsUrl')) return redirect(session()->get('adminItemsUrl'));     
        return redirect(route('admin-client-notes.index', ['slug' => $note->client->slug]));
    }
}