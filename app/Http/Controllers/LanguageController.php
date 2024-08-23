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
use App\Models\Ip;
use Session;
use App\Models\Faq;
use App\Models\Language;
use App\Models\DataFaq;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $this->params['apis'] = Api::get();
        
        $query = Language::orderBy('sort', 'asc');

        $this->params['items'] = $query->paginate(200);
        
        return $this->view('languages.index');
    }

    public function add(Request $request)
    {
        return $this->view('languages.add');
    }
    
    public function save(Request $request)
    {
        $input = $request->all();
        
        $rules = [
            'name' => 'required', 
            'code' => 'required|unique:languages,code', 
        ];

        $validation = Validator::make($input, $rules);

        if ($validation->passes()) {
            $obj = new Language;
            
            $obj->name = $input['name'];
            $obj->code = $input['code'];
            
            $obj->save();
            
            if ($this->api) {
                return Redirect::route('apis.languages.index', ['api_id' => $this->api->id]);
            } else {
                return Redirect::route('languages.index', []);
            }
        }
        
        if ($this->api) {
            return Redirect::route('apis.languages.add', ['api_id' => $this->api->id])->
                withInput()->withErrors($validation)->with('message', 'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('apis.languages.add', ['api_id' => $this->api->id])->
                withInput()->withErrors($validation)->with('message', 'Некоторые поля заполнены не верно.');
        }
    }
    
    public function displaceSort(Request $request)
    {
        $thisObj = Language::where('id', '=', $request->obj_id)->first();

        if ($thisObj) {
            if ($request->obj_to_id > 0) {
                $selectObj = Language::where('id', '=', $request->obj_to_id)->first();
                
                if ($selectObj) {
                    $nextObj = Language::where('sort', '>', $selectObj->sort)->where('id', '!=', $selectObj->id)->orderBy('sort', 'asc')->first();
                    
                    if ($nextObj) {
                        $thisObj->sort = (floatval($selectObj->sort)+floatval($nextObj->sort))/2;
                    } else {
                        $thisObj->sort = floatval($selectObj->sort) + 0.1;
                    }
                    
                    $thisObj->save();
                }
            } else {
                $prevObj = Language::where('sort', '<', $thisObj->sort)->where('id', '!=', $thisObj->id)->orderBy('sort', 'desc')->first();
                $nextObj = Language::where('sort', '>', $thisObj->sort)->where('id', '!=', $thisObj->id)->orderBy('sort', 'asc')->first();
           
                //exit(($prevObj?$prevObj->sort:0).'-'.($thisObj?$thisObj->sort:0).'-'.($nextObj?$nextObj->sort:0));
            
                switch ($request->type) {
                    case 'up':
                        if ($prevObj) {
                            $tmpsort = $prevObj->sort;
                            $prevObj->sort = $thisObj->sort;
                            $thisObj->sort = $tmpsort;
                        
                            $prevObj->save();
                            $thisObj->save();
                        }
                    
                        break;
                    case 'down':
                        if ($nextObj) {
                            $tmpsort = $nextObj->sort;
                            $nextObj->sort = $thisObj->sort;
                            $thisObj->sort = $tmpsort;
                        
                            $nextObj->save();
                            $thisObj->save();
                        }
                    
                        break;
                }
            }
        }

        return Response::json(['status' => 1]);
    }
    
    public function edit(Request $request)
    {
        $obj = Language::where('id', '=', $request->language_id)->firstOrFail();

        $this->params['obj'] = $obj;

        return $this->view('languages.edit');
    }
    
    public function update(Request $request)
    {
        $obj = Language::where('id', '=', $request->language_id)->firstOrFail();
        
        $input = $request->all();
        
        $rules = [
            'name' => 'required', 
            'code' => 'required|unique:languages,code,'.$obj->id, 
        ];

        $validation = Validator::make($input, $rules);

        if ($validation->passes()) {
            $obj->name = $input['name'];
            $obj->code = $input['code'];
            
            $obj->save();
            
            if ($this->api) {
                return Redirect::route('apis.languages.index', ['api_id' => $this->api->id]);
            } else {
                return Redirect::route('languages.index', []);
            }
        }
        
        if ($this->api) {
            return Redirect::route('apis.languages.edit', ['api_id' => $this->api->id, 'language_id' => $obj->id])->
                withInput()->withErrors($validation)->with('message', 'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('languages.edit', ['language_id' => $obj->id])->
                withInput()->withErrors($validation)->with('message', 'Некоторые поля заполнены не верно.');
        }
    }
    
    public function destroy(Request $request)
    {
        $language = Language::where('id', '=', $request->language_id)->firstOrFail();
        
        $dataFaq = DataFaq::where('language_id', '=', $language->id);
        
        if ($dataFaq->count()) {
            if ($this->api) {
                return Redirect::route('apis.languages.index', ['api_id' => $this->api->id])->with('message',
                    'Данный язык удалить нельзя к нему привязан вопрос - ответ.');
            } else {
                return Redirect::route('languages.index', [])->with('message',
                    'Данный язык удалить нельзя к нему привязан вопрос - ответ.');
            }
        } else {
            $language->delete();
            
            if ($this->api) {
                return Redirect::route('apis.languages.index', ['api_id' => $this->api->id]);
            } else {
                return Redirect::route('languages.index', []);
            }
        }
    }
}
