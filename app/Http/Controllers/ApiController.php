<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;
use App\Models\Server;

use App\Models\Api;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Storage;
use Carbon\Carbon;
use File;
use Response;
use DB;
use Session;

class ApiController extends Controller
{
    public function __construct(Request $request, Server $server)
    {
        parent::__construct($request); 

        $this->server = $server;
    }
    
    public function index()
    {
        $this->params['apis'] = Api::get();
        
        Session::forget('this_api');
        
        return $this->view('apis.index');
    }
    
    public function add()
    {
        return $this->view('apis.add');
    }

    public function edit(Api $api)
    {
        $this->params['api'] = $api;      
        
        return $this->view('apis.edit');
    }

    public function save(Request $request)
    {
        $input = $request->all();
        
        $validation = Validator::make($input, Api::$rules);

        if ($validation->passes()) {
            $data = [];
            
            if(isset($input['name'])){
                $data['name'] = $input['name'];
            }
            
            if(isset($input['package'])){
                $data['package'] = $input['package'];
            }
            
            if(isset($input['key'])){
                $data['key'] = $input['key'];
            }
            
            if(isset($input['ip'])){
                $data['ip'] = $input['ip'];
            }
            
            $request->file('img')->move(public_path('images-api'), $request->file('img')->getClientOriginalName()); 
            $data = $request->except(['img']);
            $data['img'] = $request->file('img')->getClientOriginalName(); 
            
            Api::create($data);

            return Redirect::route('apis.index');
        }

        return Redirect::route('apis.add')->withInput()->withErrors($validation)->
            with('message', 'Некоторые поля заполнены не верно.');
    }

    public function update(Request $request, Api $api)
    {
        $input = $request->all();
        
        $rules = Api::$rules;
        
        unset($rules['img']);
        
        $rules['key'] = $rules['key'] . ',' . $api->id;
        
        $validation = Validator::make($input, $rules);

        if ($validation->passes()) {
            if(isset($input['name'])){
                $api->name = $input['name'];
            }
            if(isset($input['package'])){
                $api->package = $input['package'];
            }
            if(isset($input['key'])){
                $api->key = $input['key'];
            }
            
            if($request->file('img')){
                $request->file('img')->move(public_path('images-api'), $request->file('img')->getClientOriginalName()); 
                $api->img = $request->file('img')->getClientOriginalName(); 
            }
            
            $api->save();

            return Redirect::route('apis.index');
        }
        
        return Redirect::route('apis.edit', $api->id)->withInput()->
            withErrors($validation)->with('message', 'Некоторые поля заполнены не верно.');
    }
    
    public function destroy(Api $api)
    {
        $api->delete();

        return Redirect::route('apis.index');
    } 
}
