<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;
use App\Models\Server;

use App\Models\Token;
use App\Models\Ip;
use App\Models\Badip;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Storage;
use Carbon\Carbon;
use File;
use Response;
use DB;
use Session;

class TokenController extends Controller
{
    public function __construct(Request $request, Server $server)
    {
        parent::__construct($request); 

        $this->server = $server;
    }
    
    public function index(Request $request)
    {
        Session::put('index_tokens_url', url()->full());
        
        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'id';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = Token::select('tokens.*');

        $query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' : 'asc');
        
        
        
        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }
                
                $value = urldecode($value);

                switch ($key) {
                    case 'ip':
                    case 'name':
                        $query->where($key, 'like', '%' . ($value) . '%');

                        break;
                    case 'status':
                        if (stripos($value, "all") === false) {
                            $query->whereIn($key, explode(',', trim(trim(trim($value), ','))));
                        }

                        break;
                    default:
                        $query->where($key, '=', ($value));

                        break;
                }
            }
        }

        $this->params['items'] = $query->paginate(800);
        $this->params['countall'] = $query->count();
        
        foreach ($this->params['items'] as $item) {
            $item->isActive();
        }

        return $this->view('tokens.index');
    }

    public function item(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Token::where('id', '=', $request->id)->firstOrFail();
        }

        return $this->view('tokens.item');
    }

    public function save(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Token::where('id', '=', $request->id)->firstOrFail();
        } else {
            $this->params['item'] = new Token;
        }

        $dataitem = request('dataitem');

        if (!is_array($dataitem)) {
            $dataitem = [];
        }

        $rules = [
            "short_code" => "required|size:6|regex:#^[A-Za-z0-9]{6}$#sui|unique:tokens,short_code",
            "days" => "required|numeric",
            "email" => "email",
        ];

        if ($this->params['item']->id) {
            unset($dataitem["short_code"]);
            
            $rules['short_code'] = 'required|unique:tokens,short_code' . ($this->params['item'] ?
                ',' . $this->params['item']->id : '');
                
            unset($rules['short_code']);
        }
        
        if (isset($dataitem["short_code"])) {
            $dataitem["short_code"] = strtolower($dataitem["short_code"]);
        }

        $validation = Validator::make($dataitem, $rules);

        if ($validation->passes()) {
            foreach ($dataitem as $key => $value) {
                switch ($key) {
                    default:
                        $this->params['item']->{$key} = $value;
                        break;
                }
            }

            $this->params['item']->saveDB();

            if (Session::get('index_tokens_url')) {
                return Redirect::to(Session::get('index_tokens_url'));
            } else {
                return Redirect::route('tokens.index', []);
            }
        }

        if ($this->params['item']->id) {
            return Redirect::route('tokens.edit', ['id' => $this->params['item']->id])->
                withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('tokens.add', [])->withInput()->withErrors($validation)->
                with('message', 'Некоторые поля заполнены не верно.');
        }
    }

    public function delete(Request $request)
    {
        $this->params['item'] = Token::where('id', '=', $request->id)->firstOrFail();
        $this->params['item']->deleteDB();
        if (Session::get('index_tokens_url')) {
            return Redirect::to(Session::get('index_tokens_url'));
        } else {
            return Redirect::route('tokens.index', []);
        }
    }

    public function keyGenerate(Request $request)
    {
        return Response::json(['status' => 1, 'key' => Token::getUniqueShortCode()]);
    }

    public function clearAppId(Request $request)
    {
        $this->params['item'] = Token::where('id', '=', $request->id)->firstOrFail();
        
        $this->params['item']->app_id = null;
        
        $this->params['item']->saveDB();

        return Response::json(['status' => 1]);
    }

    public function statusUpdate(Request $request)
    {
        $this->params['item'] = Token::where('id', '=', $request->id)->firstOrFail();
        
        $this->params['item']->status = $request->status;
        
        $this->params['item']->saveDB();

        return Response::json(['status' => 1]);
    }

    public function settings(Request $request)
    {
        return $this->view('tokens.setting');
    }

    public function settingsSave(Request $request)
    {
        \App\Helpers\Setting::value('short_key_retry_error_24h', abs(intval($request->short_key_retry_error_24h)));
        
        \App\Helpers\Setting::value('short_key_reset_limit_24h', abs(intval($request->short_key_reset_limit_24h)));
        
        \App\Helpers\Setting::value('app_id_limit_1h', abs(intval($request->app_id_limit_1h)));
        
        \App\Helpers\Setting::value('app_id_limit_24h', abs(intval($request->app_id_limit_24h)));
        
        \App\Helpers\Setting::value('tokens_secret_key', trim($request->tokens_secret_key));
        
        return Redirect::route('tokens.settings', []);
    }

    public function apiKeyActivate(Request $request)
    {
        $this->ctrIp($request);
        
        if (!$request->short_code) {
            Helper::log("short_code not passed. short_code: " . strtolower($request->short_code));
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1005, // short_code not passed // Invalid key
            ], JSON_HEX_TAG);
            exit;
        }
        
        $short_key_retry_error_24h = intval(\App\Helpers\Setting::value('short_key_retry_error_24h'));
        $short_key_reset_limit_24h = intval(\App\Helpers\Setting::value('short_key_reset_limit_24h'));
        
        $ip = Ip::where("ip", "=", $_SERVER['REMOTE_ADDR'])->first();
        
        if ($ip) {
            if ($ip->first_error_short_code_at) {
                if ($ip->first_error_short_code_at < date("Y-m-d H:i", time() - 60 * 60 * 24)) {
                    $ip->first_error_short_code_at = null;
                    $ip->count_error_short_code = 0;
                    
                    $ip->saveDB();
                }
            }
            
            if ($ip->count_error_short_code > $short_key_retry_error_24h) {
                $Badip = Badip::where("ip", "=", $_SERVER['REMOTE_ADDR'])->first();
                
                if (!$Badip) {
                    $Badip = new Badip;
                    
                    $Badip->ip = $_SERVER['REMOTE_ADDR'];
                    $Badip->status = 1;
                    
                    $Badip->saveDB();                                        
                } 
                
                if ($Badip->status == 1) {
                    Helper::log("24h retry limit. Ip: ".$ip.". short_code: " . $request->short_code);
                
                    http_response_code(500);
                    echo json_encode([
                        'status' => 0,
                        'error' => 1006, // 24h retry limit
                   ], JSON_HEX_TAG);
                    exit;
                }
            }
        }
        
        $token = Token::where('short_code', '=', $request->short_code)->first();
        
        if (!$token) {
            if (!$ip) {
                $ip = new Ip;
                
                $ip->ip = $_SERVER['REMOTE_ADDR'];
                $ip->first_error_short_code_at = date("Y-m-d H:i");
            }
            
            if (!$ip->first_error_short_code_at) {
                $ip->first_error_short_code_at = date("Y-m-d H:i");
            }
            
            $ip->count_error_short_code = intval($ip->count_error_short_code) + 1;
                    
            $ip->saveDB();
            
            Helper::log("Key does not exist. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1005, // Key does not exist // 1005
            ], JSON_HEX_TAG);
            exit;
        }
        
        if ($token->status == "expired") {
            Helper::log("Key expired. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1007, // Key expired
            ], JSON_HEX_TAG);
            exit;
        }
        
        if ($token->status == "banned") {
            Helper::log("Key banned. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1008, // Key banned
            ], JSON_HEX_TAG);
            exit;
        }
        
        if ($token->status != "active" && $token->status != "not activated") {
            Helper::log("Key not valid status. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1005, // Key not valid status // Invalid key
            ], JSON_HEX_TAG);
            exit;
        }
        
        if ($token->reset_code_24hour_at) {
            if ($token->reset_code_24hour_at < date("Y-m-d H:i", time() - 60 * 60 * 24)) {
                $token->reset_code_24hour_at = null;
                $token->reset_code_24hour_count = 0;
                    
                $token->saveDB();
            }
        }
        
        if (!$token->first_error_short_code_at) {
            $token->reset_code_24hour_at = date("Y-m-d H:i");
        } 
        
        if ($token->reset_code_24hour_count > $short_key_reset_limit_24h) {
            Helper::log("24h reset limit. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1009, // 24h reset limit
            ], JSON_HEX_TAG);
            exit;
        }
        
        $token->reset_code_24hour_count = intval($token->reset_code_24hour_count) + 1;
        $token->saveDB();
        
        $token->activate();
        
        if (!$token->isActive()) {
            Helper::log("Key cannot be activated. short_code: " . $request->short_code);
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1005, // Key cannot be activated // Invalid key
            ], JSON_HEX_TAG);
            exit;
        }
        
        return Response::json([
            'status' => 1,
            'app_id' => $token->app_id,
            'active_seconds' => $token->getLeft(false),
            'active_date' => $token->getDateActive(),
        ]);
    }

    public function apiKeyGenerate(Request $request)
    {
        $response = [];
        
        $response['status'] = 0;
        
        if ($request->secret_key == \App\Helpers\Setting::value('tokens_secret_key')) {
            $token = null;
            
            if ($request->key) {
                $token = Token::where("short_code", "=", $request->key)->first();
            }
            
            if (!$token) {
                $days = intval($request->days);
            
                if ($days > 0) {
                    $token = new Token;
                
                    if ($request->key) {
                        $token->short_code = $request->key;
                    } else {
                        $token->short_code = Token::getUniqueShortCode();
                    }
                    
                    $token->status = "not activated";
                    $token->days = $days;
                    $token->email = $request->email;
                    $token->subscribe = $request->subscribe == "1" ? 1 : 0;
                
                    $response['status'] = 1;
                    $response['key'] = $token->short_code;
                
                    if ($token->subscribe == "1") {
                        $token->activate();
                        
                        if ($request->subscribe_time && is_numeric($request->subscribe_time)) {
                            if ($request->subscribe_time > $token->subscribe_time) {
                                $token->subscribe_time = $request->subscribe_time + 3600*24;
                            }
                        }
                    }
                
                    $token->saveDB();
                
                    $response['active_time'] = null;
                
                    $seconds = $token->getActiveSeconds();
                    if ($seconds) {
                        $response['active_time'] = time() + $seconds;
                    }
                }
            }
            
            if ($request->key) {
                if ($token) {
                    if ($request->subscribe_time && is_numeric($request->subscribe_time)) {
                        if (true) {
                            $token->subscribe = $request->subscribe == "1" ? 1 : 0;
                            $token->subscribe_time = $request->subscribe_time + 3600*24;
                            if ($request->subscribe_time > time()) {
                                $token->status = "active";
                                
                                if (!$token->activated_at) {
                                    $token->activated_at = date("Y-m-d H:i");
                                }
                                if (!$token->app_id) {
                                    $token->app_id = Token::getUniqueAppId();
                                }
                            }
                            $token->saveDB();
                            $response['status'] = 1;
                        }
                    }
                }
            }
        }
        
        return Response::json($response);
    }

    public function tokensSelectDelete(Request $request)
    {
        if (is_array($request->ids) && count($request->ids) > 0) {
            foreach (Token::whereIn("id", $request->ids)->get() as $token) {
                $token->delete();
            }
            
            return Response::json(['status' => 1]);
        }

        return Response::json(['status' => 0]);
    }
}
