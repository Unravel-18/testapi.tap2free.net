<?php

namespace App\Http\Controllers;

use Validator;
use Route;
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
use App\Models\ApiFaq;

class FaqController extends Controller
{
    public function all(Request $request)
    {
        $this->params['apis'] = Api::get();
        
        $language = Language::where('code', '=', 'en')
            ->first();
        
        if (!$language) {
            $language = Language::first();
        }
        
        if ($language) {
            $query = Faq::select('faqs.*', 'data_faqs.question', 'data_faqs.answer')
                ->leftJoin('data_faqs', function ($join) use ($language) {
                    $join->on('faqs.id', '=', 'data_faqs.faq_id')
                        ->where('data_faqs.language_id', '=', $language->id);
                })
                ->orderBy('faqs.sort', 'asc');

            $this->params['items'] = $query->paginate(200);
        } else {
            $query = Faq::select('faqs.*')
                ->orderBy('faqs.sort', 'asc');

            $this->params['items'] = $query->paginate(200);
        }
        
        return $this->view('faq.all');
    }

    public function faqApis(Request $request)
    {
        $query = Faq::where('faqs.id', '=', $request->faq_id);
        
        $language = Language::where('code', '=', 'en')->first();
        
        if (!$language) {
            $language = Language::first();
        }
        
        if ($language) {
            $query->select('faqs.*', 'data_faqs.question', 'data_faqs.answer')
                ->leftJoin('data_faqs', function ($join) use ($language) {
                    $join->on('faqs.id', '=', 'data_faqs.faq_id')
                        ->where('data_faqs.language_id', '=', $language->id);
                });
        }
        
        $faq = $query->firstOrFail();
        
        $query = Api::leftJoin('api_faqs', function ($join) use ($faq) {
            $join->on('api_faqs.api_id', '=', 'apis.id')
                ->where('api_faqs.faq_id', '=', $faq->id);
        })->select(
            'apis.*', 
            'api_faqs.api_id', 
            'api_faqs.faq_id', 
            'api_faqs.status_faq'
        );
        
        $this->params['apis'] = $query->get();
        
        $this->params['faq'] = $faq;
        
        return $this->view('faq.apis');
    }

    public function faqApisSave(Request $request)
    {
        $faq = Faq::where('id', '=', $request->faq_id)->firstOrFail();
        
        $apis = Api::get();
        
        foreach ($apis as $api) {
            $apiFaq = ApiFaq::where('api_id', '=', $api->id)
                ->where('faq_id', '=', $faq->id)
                ->first();
            
            if (!$apiFaq) {
                $apiFaq = new ApiFaq;
                
                $apiFaq->api_id = $api->id;
                $apiFaq->faq_id = $faq->id;
            }
            
            $apiFaq->status_faq = request('apis.'.$api->id.'.status_faq') ? 1 : 0;  
            
            $apiFaq->save();
        }
        
        if ($this->api) {
            return Redirect::route('apis.faq.apis', ['api_id' => $this->api->id, 'faq_id' => $faq->id])->with('message', 'Сохранено !!!');
        } else {
            return Redirect::route('faq.apis', ['faq_id' => $faq->id])->with('message', 'Сохранено !!!');
        }
    }
    
    public function index(Request $request)
    {
        $this->params['apis'] = Api::get();
        
        $language = Language::where('code', '=', 'en')->first();
        
        if (!$language) {
            $language = Language::first();
        }
        
        if ($language) {
            $query = Faq::select('faqs.*', 'data_faqs.question', 'data_faqs.answer')
                ->leftJoin('api_faqs', 'api_faqs.faq_id', '=', 'faqs.id')
                ->where('api_faqs.api_id', '=', $this->api->id)
                ->where('api_faqs.status_faq', '=', 1)
                ->leftJoin('data_faqs', function ($join) use ($language) {
                    $join->on('faqs.id', '=', 'data_faqs.faq_id')
                        ->where('data_faqs.language_id', '=', $language->id);
                })
                ->orderBy('faqs.sort', 'asc');

            $this->params['items'] = $query->paginate(200);
        } else {
            $query = Faq::select('faqs.*')
                ->leftJoin('api_faqs', 'api_faqs.faq_id', '=', 'faqs.id')
                ->where('api_faqs.api_id', '=', $this->api->id)
                ->where('api_faqs.status_faq', '=', 1)
                ->orderBy('faqs.sort', 'asc');

            $this->params['items'] = $query->paginate(200);
        }
        
        return $this->view('faq.index');
    }

    public function add(Request $request)
    {
        $this->params['languages'] = Language::get();
        
        return $this->view('faq.add');
    }
    
    public function displaceSort(Request $request)
    {
        $thisObj = Faq::where('id', '=', $request->obj_id)->first();

        if ($thisObj) {
            if ($request->obj_to_id > 0) {
                $selectObj = Faq::where('id', '=', $request->obj_to_id)->first();
                
                if ($selectObj) {
                    $nextObj = Faq::where('sort', '>', $selectObj->sort)->where('id', '!=', $selectObj->id)->orderBy('sort', 'asc')->first();
                    
                    if ($nextObj) {
                        $thisObj->sort = (floatval($selectObj->sort)+floatval($nextObj->sort))/2;
                    } else {
                        $thisObj->sort = floatval($selectObj->sort) + 0.1;
                    }
                    
                    $thisObj->save();
                }
            } else {
                $prevObj = Faq::where('sort', '<', $thisObj->sort)->where('id', '!=', $thisObj->id)->orderBy('sort', 'desc')->first();
                $nextObj = Faq::where('sort', '>', $thisObj->sort)->where('id', '!=', $thisObj->id)->orderBy('sort', 'asc')->first();
           
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
    
    public function destroy(Request $request)
    {
        $obj = Faq::where('id', '=', $request->faq_id)->firstOrFail();

        $obj->delete();
        
        if ($this->api) {
            return Redirect::route('apis.faq.index', ['api_id' => $this->api->id]);
        } else {
            return Redirect::route('faq.all', []);
        }
    }
    
    public function destroyApi(Request $request)
    {
        $apiFaq = ApiFaq::where('api_id', '=', $this->api->id)
                ->where('faq_id', '=', $request->faq_id)
                ->first();
                
        if ($apiFaq) {
            $apiFaq->status_faq = 0;
            
            $apiFaq->save();
        }
        
        if ($this->api) {
            return Redirect::route('apis.faq.index', ['api_id' => $this->api->id]);
        } else {
            return Redirect::route('faq.all', []);
        }
    }
    
    public function save(Request $request)
    {
        $input = $request->all();
        
        if (isset($input['questions']) && 
            isset($input['answers']) && 
            is_array($input['questions']) && 
            is_array($input['answers']) && 
            count($input['questions']) == count($input['answers'])) {
            
            $faq = new Faq;
                        
            $faq->save();     
            
            if ($this->api) {
                $apiFaq = ApiFaq::where('api_id', '=', $this->api->id)
                    ->where('faq_id', '=', $faq->id)
                    ->first();
                
                if (!$apiFaq) {
                    $apiFaq = new ApiFaq;
                
                    $apiFaq->api_id = $this->api->id;
                    $apiFaq->faq_id = $faq->id;
                }
            
                $apiFaq->status_faq = 1;
                
                $apiFaq->save();
            }       
            
            foreach ($input['questions'] as $language_id => $question) {
                $language = Language::where('id', '=', $language_id)->first();
                
                if ($language) {
                    $dataFaq = DataFaq::where('faq_id', '=', $faq->id)
                        ->where('language_id', '=', $language->id)
                        ->first();
                    
                    if (!$dataFaq) {
                        $dataFaq = new DataFaq;
                        $dataFaq->faq_id = $faq->id;
                        $dataFaq->language_id = $language->id;
                    }
                    
                    $dataFaq->question = $input['questions'][$language_id];
                    $dataFaq->answer = $input['answers'][$language_id];
                    
                    if ($dataFaq->question && $dataFaq->answer) {
                        $dataFaq->save();
                    } else {
                        $dataFaq->delete();
                    }
                }
            }
            
            if (Session::get('select_faq_id')) {
                $selectFaq = Faq::where('id', '=', Session::get('select_faq_id'))->first();
                
                if ($selectFaq) {
                    $nextFaq = Faq::where('sort', '>', $selectFaq->sort)->where('id', '!=', $selectFaq->id)->orderBy('sort', 'asc')->first();
                    
                    if ($nextFaq) {
                        $faq->sort = (floatval($selectFaq->sort)+floatval($nextFaq->sort))/2;
                    } else {
                        $faq->sort = floatval($selectFaq->sort) + 0.5;
                    }
                    
                    $faq->save();
                }
                
                Session::forget('select_faq_id');
            }
        }
        
        if ($this->api) {
            return Redirect::route('apis.faq.index', ['api_id' => $this->api->id]);
        } else {
            return Redirect::route('faq.all', []);
        }
    }
    
    public function edit(Request $request)
    {
        $obj = Faq::where('id', '=', $request->faq_id)->firstOrFail();

        $this->params['languages'] = Language::get();
        
        $this->params['obj'] = $obj;

        return $this->view('faq.edit');
    }
    
    public function update(Request $request)
    {
        $faq = Faq::where('id', '=', $request->faq_id)->firstOrFail();
        
        $input = $request->all();
        
        if (isset($input['questions']) && 
            isset($input['answers']) && 
            is_array($input['questions']) && 
            is_array($input['answers']) && 
            count($input['questions']) == count($input['answers'])) {
            
            foreach ($input['questions'] as $language_id => $question) {
                $language = Language::where('id', '=', $language_id)->first();
                
                if ($language) {
                    $dataFaq = DataFaq::where('faq_id', '=', $faq->id)
                        ->where('language_id', '=', $language->id)
                        ->first();
                    
                    if (!$dataFaq) {
                        $dataFaq = new DataFaq;
                        $dataFaq->faq_id = $faq->id;
                        $dataFaq->language_id = $language->id;
                    }
                    
                    $dataFaq->question = $input['questions'][$language_id];
                    $dataFaq->answer = $input['answers'][$language_id];
                    
                    if ($dataFaq->question && $dataFaq->answer) {
                        $dataFaq->save();
                    } else {
                        $dataFaq->delete();
                    }
                }
            }
        }
        
        if ($this->api) {
            return Redirect::route('apis.faq.index', ['api_id' => $this->api->id]);
        } else {
            return Redirect::route('faq.all', []);
        }
    }
    
    public function apiGet(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);
        
        $code = 'en';
        
        if ($request->lng) {
            $code = $request->lng;
        }
        
        $language = Language::where('code', '=', $code)
            ->firstOrFail();
        
        $query = Faq::select('faqs.*', 'data_faqs.question', 'data_faqs.answer')
                ->leftJoin('api_faqs', 'api_faqs.faq_id', '=', 'faqs.id')
                ->where('api_faqs.api_id', '=', $this->api->id)
                ->where('api_faqs.status_faq', '=', 1)
                ->leftJoin('data_faqs', function ($join) use ($language) {
                    $join->on('faqs.id', '=', 'data_faqs.faq_id')
                        ->where('data_faqs.language_id', '=', $language->id);
                })
                ->orderBy('faqs.sort', 'asc');
        
        $response = [];
        
        foreach ($query->get() as $key => $faq) {
            $response[$key] = [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ];
        }
        
        return Response::json($response, 200, [], JSON_HEX_TAG);
    }

    public function saveSelectChecked(Request $request)
    {
        if ($request->select_id) {
            Session::put('select_faq_id', $request->select_id);
        } else {
            Session::forget('select_faq_id');
        }

        return Response::json(['status' => 1]); 
    }
}
