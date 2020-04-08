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
use App\Models\Label;
use JavaScript;
use URL;
use Artisan;

class SettingsLabelsController extends Controller
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
        $groups = Label::select('group')->orderBy('group','asc')->distinct()->get();
        $breadcrumb='settingsLabelGroups';
        return view('admin.partials.settings.labels.groups', compact('groups','breadcrumb'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function labels(Request $request)
    {
        $labels = Label::whereGroup($request->group)->get();        
        $locales = Label::whereGroup($request->group)->select('locale')->distinct()->get();
        $translations = [];
        foreach($labels as $key => $label){
            $translations[$label->key][$label->locale] = $label;
        }   

        $group = $request->group;
        JavaScript::put(['translations' => $translations]);
        JavaScript::put(['locales' => $locales]);
        $breadcrumb='settingsLabels';
        return view('admin.partials.settings.labels.labels', compact('translations', 'locales', 'group', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $label = new Label();
        $label->value = $request->label;
        $label->locale = $request->locale;
        $label->group = $request->group;;
        $label->key = $request->key;
        $label->status = 0;
        $label->save();
    }

    public function update(Request $request)
    {
        $label = Label::find($request->labelId);
        $label->value = $request->label;
        $label->save();
        echo 'Label saved!';
    }   

    
    public function search(Request $request)
    {  
        if(isset($request->value)) session()->put('searchLabel',$request->value);
        $search=session()->get('searchLabel');
        $labels=Label::where('value', 'like', '%'.$search.'%')->orWhere('key', 'like', '%'.$search.'%')->get();            
        $locales = Label::where('value', 'like', '%'.$search.'%')->orWhere('key', 'like', '%'.$search.'%')->select('locale')->distinct()->get();
        $translations = [];
        foreach($labels as $key => $label){
            $translations[$label->group.$label->key][$label->locale] = $label;
        } 
        JavaScript::put(['translations' => $translations]);
        JavaScript::put(['locales' => $locales]);
        $breadcrumb='settingsLabels';
        return view('admin.partials.settings.labels.search', compact('translations', 'locales', 'breadcrumb', 'search'));
       
    }
    public function import()
    {
        Artisan::call('translations:reset');
        Artisan::call('translations:import');
        //exec('bash -c "exec nohup setsid php artisan translations:reset > /dev/null 2>&1 &"');
        //exec('bash -c "exec nohup setsid php artisan translations:import > /dev/null 2>&1 &"');
        return redirect(route('admin-settings-labels.index'));
    }

    public function export(Request $request)
     {  
        Artisan::call('translations:export', ['group' => $request->group]);
        //exec('bash -c "exec nohup setsid php artisan translations:export '.$request->group.' > /dev/null 2>&1 &"');
        return redirect(route('admin-settings-labels.index'));
     } 

     public function exportAllGroups(Request $request)
     {
         $groups = Label::select('group')->orderBy('group','asc')->distinct()->get();
         foreach ($groups as $key => $group) {
            Artisan::call('translations:export', ['group' => $group->group]);
             //exec('bash -c "exec nohup setsid php artisan translations:export '.$group->group.' > /dev/null 2>&1 &"');
         }
         return redirect(route('admin-settings-labels.index'));
     }
    
}
