<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Api;
use App\Models\Android;
use Carbon\Carbon;
use DB;
use Session;
use App\Models\Googletoken;
use App\Models\Token;
use App\Helpers\Helper;
use App\Models\Prokey;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $api = null;
    protected $googletoken = null;

    protected $android = null;

    protected $params = [];
    public $auth_app_id_nm = 0;
    public $app_encryption_nm = 0;
    
    public $app_token = null;
    
    public $prokey = null;

    public function __construct(Request $request)
    {
        //file_put_contents(__dir__ . '/log.txt', date('Y-m-d H:i:s') . " " . json_encode(getallheaders()) . "\n", FILE_APPEND);

        if ($request->api_id) {
            $this->api = Api::where('id', '=', $request->api_id)->first();
        } elseif ($request->api_key) {
            $this->api = Api::where('key', '=', $request->api_key)->first();
        } else {
            $this->api = null;
        }

        if ($this->api) {
            \App\Helpers\Helper::$apiid = $this->api->id;
            //Session::put('this_api', $this->api);
        } else {
            \App\Helpers\Helper::$apiid = 0;
        }

        $apis_open_api = [];

        $sql = Api::select('apis.*', 'settings.value as app_api_auth')->leftJoin('settings',
            'settings.key', '=', DB::raw("concat('app_api_auth:', `apis`.`id`)"))->where(function
            ($query)
        {
            $query->where('settings.value', '!=', 'true')->orWhere('settings.value', '=', null); }
        );

        //echo $sql->toSql();exit;

        foreach ($sql->get() as $api) {
            $apis_open_api[] = $api->key;
        }

        $this->params['open_api'] = implode(', ', $apis_open_api);

        $this->params['api'] = $this->api;

        $this->params['count_connections_404'] = DB::table('servers')->where('count_connections',
            '=', '404')->count();
            
        $app_id = array_get(getallheaders(), 'app_id');

        if (!$app_id) {
            $app_id = array_get(getallheaders(), 'app-id');
        }
        
        if (array_get(getallheaders(), 'pro-id')) {
            $prokey = Prokey::where("pro_id", "=", array_get(getallheaders(), 'pro-id'))->first();
            
            if ($prokey) {
                if (!$prokey->pro_id_limit_1h_at) {
                    $prokey->pro_id_limit_1h_at = date("Y-m-d H:i:s");
                    $prokey->pro_id_count_1h = 0;
                }
                
                if (!$prokey->pro_id_limit_24h_at) {
                    $prokey->pro_id_limit_24h_at = date("Y-m-d H:i:s");
                    $prokey->pro_id_count_24h = 0;
                }
                
                if (date_create($prokey->pro_id_limit_1h_at)->getTimestamp() < time() - 60 * 60) {
                    $prokey->pro_id_limit_1h_at = date("Y-m-d H:i:s");
                    $prokey->pro_id_count_1h = 0;
                }
                
                if (date_create($prokey->pro_id_limit_24h_at)->getTimestamp() < time() - 60 * 60 * 24) {
                    $prokey->pro_id_limit_24h_at = date("Y-m-d H:i:s");
                    $prokey->pro_id_count_24h = 0;
                }
                
                $prokey->pro_id_count_1h = $prokey->pro_id_count_1h + 1;
                $prokey->pro_id_count_24h = $prokey->pro_id_count_24h + 1;
                
                if ($prokey->pro_id_count_1h > \App\Helpers\Setting::value('pro_id_limit_1h')) {
                    $prokey->status = 2;
                }
                
                if ($prokey->pro_id_count_24h > \App\Helpers\Setting::value('pro_id_limit_24h')) {
                    $prokey->status = 2;
                }
                
                $prokey->save();
                
                if ($prokey->isAtive()) {
                    $this->prokey = $prokey;
                }
            }
        }
        
        if ($this->api && $app_id) {
            if ($app_id === \App\Helpers\Setting::value('app_encryption_app_id:' . $this->api->id)) {
                $this->app_encryption_nm = 1;
            } 
            
            if (!\App\Helpers\Setting::value('google_tokens:' . $this->api->id) && 
                $app_id === \App\Helpers\Setting::value('app_encryption_app_id_2_2:' . $this->api->id)) {
                $this->app_encryption_nm = 2;
            }
            
            if ($app_id === \App\Helpers\Setting::value('app_encryption_app_id_3:' . $this->api->id)) {
                $this->app_encryption_nm = 3;
            } 
            
            if ($this->app_encryption_nm == "0") {
                $token = Token::where("app_id", "=", $app_id)->first();
                
                if ($token) {
                    $app_id_limit_1h = intval(\App\Helpers\Setting::value('app_id_limit_1h'));
                    $app_id_limit_24h = intval(\App\Helpers\Setting::value('app_id_limit_24h'));
                    
                    if ($token->app_hour_at) {
                        if ($token->app_hour_at < date("Y-m-d H:i", time() - 60 * 60 * 1)) {
                            $token->app_hour_at = date("Y-m-d H:i");
                            $token->app_hour_count = 0;
                        }
                    } else {
                        $token->app_hour_at =  date("Y-m-d H:i");
                        $token->app_hour_count = 0;
                    }
                    
                    if ($token->app_24hour_at) {
                        if ($token->app_24hour_at < date("Y-m-d H:i", time() - 60 * 60 * 24)) {
                            $token->app_24hour_at = date("Y-m-d H:i");
                            $token->app_24hour_count = 0;
                        }
                    } else {
                        $token->app_24hour_at =  date("Y-m-d H:i");
                        $token->app_24hour_count = 0;
                    }
                    
                    if ($token->app_24hour_count > $app_id_limit_24h) {
                        $token->saveDB();
                        
                        Helper::log("API limit. short_code: " . $token->short_code);
            
                        http_response_code(500);
                        echo json_encode([
                            'status' => 0,
                            'error' => 1010, // App-id limit 24h
                        ], JSON_HEX_TAG);
                        exit;
                    }
                    
                    if ($token->app_hour_count > $app_id_limit_1h) {
                        $token->saveDB();
                        
                        Helper::log("API limit. short_code: " . $token->short_code);
                        
                        http_response_code(500);
                        echo json_encode([
                            'status' => 0,
                            'error' => 1011, // App-id limit 1h
                        ], JSON_HEX_TAG);
                        exit;
                    }
                    
                    $token->app_hour_count = intval($token->app_hour_count) + 1;
                    $token->app_24hour_count = intval($token->app_24hour_count) + 1;
                    
                    $token->saveDB();
                    
                    $this->app_encryption_nm = 4;
                    $this->app_token = $token;
                }
            }
        }
        
        if ($this->api && $app_id && 
            \App\Helpers\Setting::value('app_api_auth:' . $this->api->id) && 
            $this->app_encryption_nm == "0" &&
            \App\Helpers\Setting::value('google_tokens:' . $this->api->id)) {
            $app_id_md5 = null;
            
            if (strlen($app_id) < 40) {
                $this->googletoken = Googletoken::where("token_md5", "=", $app_id)->first();
            } else {
                $app_id_md5 = md5($app_id);
                $this->googletoken = Googletoken::where("token_md5", "=", $app_id_md5)->first();
                
            if ($this->googletoken) {
            } elseif ($this->api && $app_id_md5) {
                if (!$this->api->package) {
                    Helper::log("error 1004. app_id: " . $app_id);
                    
                    http_response_code(500);
                    echo json_encode([
                        "status" => 0,
                        "error" => 1004,
                    ], JSON_HEX_TAG);
                    exit;
                }
                if (!file_exists(base_path("config/google/" . $this->api->package . ".json"))) {
                    Helper::log("error 1004. app_id: " . $app_id);
                    
                    http_response_code(500);
                    echo json_encode([
                        "status" => 0,
                        "error" => 1004,
                    ], JSON_HEX_TAG);
                    exit;
                }
                
                $res = Helper::getAppInfoByToken(
                    base_path("config/google/" . $this->api->package . ".json"), 
                    $this->api->package, 
                    $app_id
                );
                
                if ($res['status']) {
                    $this->googletoken = new Googletoken;
                    
                    $this->googletoken->api_id = $this->api->id;
                    $this->googletoken->token_md5 = $app_id_md5;
                    $this->googletoken->time = $res['time'];
                    $this->googletoken->device_verdict = $res['deviceVerdict'];
                    $this->googletoken->app_verdict = $res['appVerdict'];
                    $this->googletoken->account_verdict = $res['accountVerdict'];
                    $this->googletoken->status = 1;
                } else {
                    Helper::log("error 1004. app_id: " . $app_id);
                    
                    http_response_code(500);
                    echo json_encode([
                        "status" => 0,
                        "error" => 1004,
                    ], JSON_HEX_TAG);
                    exit;
                }
            } else {
                Helper::log("error 1004. app_id: " . $app_id);
                    
                http_response_code(500);
                echo json_encode([
                    "status" => 0,
                    "error" => 1004,
                ], JSON_HEX_TAG);
                exit;
            }
            
            $this->googletoken->ip = $_SERVER['REMOTE_ADDR'];
            
            $this->googletoken->save();
            }
        }
    }

    protected function initUser($request)
    {
        $android_id = $request->android_id ? $request->android_id : array_get(getallheaders
            (), 'android_id');

        if ($android_id) {
            $this->android = Android::where('api_id', '=', $this->api->id)->where('android_id',
                '=', $android_id)->first();

            if (!$this->android) {
                $this->android = new Android();

                $this->android->android_id = $android_id;
                $this->android->last_at = Carbon::now()->toDateTimeString();
                $this->android->points = 2000;
                $this->android->level = null;
                $this->android->invite = str_random(7);
                $this->android->invite_activ = 0;
                $this->android->android_refer_invite_id = null;
                $this->android->api_id = $this->api->id;

                $this->android->save();
            }
        }

        if (!$this->android) {
            //$this->android = new Android;
        }
    }

    protected function view($template)
    {
        return view($template, $this->params);
    }

    protected function ctrIp(Request $request)
    {
        $request->ip();
        
        if (DB::table('badips')->where('status', '=', '1')->where('ip', '=', $request->ip())->count() > 0) {
            Helper::log("error badip");
                    
            http_response_code(500);
            echo json_encode([
                "status" => 0,
                "error" => 1001,
            ], JSON_HEX_TAG);
            exit;
        }
    }

    protected function authHeader()
    {
        if (\Route::currentRouteName() == 'settings.get') {
            return true;
        }

        if (!$this->api) {
            abort(404);
        }

        $app_id = array_get(getallheaders(), 'app_id');

        if (!$app_id) {
            $app_id = array_get(getallheaders(), 'app-id');
        }

        if (\App\Helpers\Setting::value('app_api_auth:' . $this->api->id)) {
            if ($this->app_encryption_nm == 4) {
                if ($this->app_token) {
                    if ($this->app_token->status == "expired") {
                        http_response_code(500);
                        echo json_encode([
                            'status' => 0,
                            'error' => 1007, // Key expired
                        ], JSON_HEX_TAG);
                        exit;
                    }
        
                    if ($this->app_token->status == "banned") {
                        http_response_code(500);
                        echo json_encode([
                            'status' => 0,
                            'error' => 1008, // Key banned
                        ], JSON_HEX_TAG);
                        exit;
                    }
                    
                    if ($this->app_token->isActive()) {
                        $this->auth_app_id_nm = 4;
                    
                        return true;
                    } else {
                        Helper::log("app_token not Active. short_code: " . $this->app_token->short_code);
                        
                        http_response_code(500);
                        echo json_encode([
                            "status" => 0,
                            "error" => 1005,
                        ], JSON_HEX_TAG);
                        exit;
                    }
                } else {
                    Helper::log("error 1002");
                        
                    http_response_code(500);
                    echo json_encode([
                        "status" => 0,
                        "error" => 1002,
                    ], JSON_HEX_TAG);
                    exit;
                }
            } elseif (\App\Helpers\Setting::value('google_tokens:' . $this->api->id) && 
                $this->app_encryption_nm == "0"
            ) {
                if ($this->googletoken) {
                    if ($this->googletoken->time > time() - 3600*24*1 && 
                        $this->googletoken->status == "1") {
                        if (
                            //$this->googletoken->device_verdict == "MEETS_DEVICE_INTEGRITY" && 
                            $this->googletoken->app_verdict == "PLAY_RECOGNIZED" && 
                            $this->googletoken->account_verdict == "LICENSED"
                        ) {
                            $this->auth_app_id_nm = 2;
                        } else {
                            $this->auth_app_id_nm = 3;
                        }
                        
                        return true;
                    } else {
                        Helper::log("error 1003. token_md5: " . $this->googletoken->token_md5);
                        
                        http_response_code(500);
                        echo json_encode([
                            "status" => 0,
                            "error" => 1003,
                        ], JSON_HEX_TAG);
                        exit;
                    }
                } else {
                    Helper::log("error 1003. not googletoken");
                    
                    http_response_code(500);
                    echo json_encode([
                            "status" => 0,
                            "error" => 1002,
                    ], JSON_HEX_TAG);
                    exit;
                }
            } else {
                if (\App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id)) {
                    if ($app_id === \App\Helpers\Setting::value('app_encryption_app_id:' . $this->api->id)) {
                        $this->auth_app_id_nm = 1;
                        return true;
                    }
                } else {
                    if ($app_id === \App\Helpers\Setting::value('app_id:' . $this->api->id) || 
                        $app_id === \App\Helpers\Setting::value('app_encryption_app_id:' . $this->api->id)) {
                        $this->auth_app_id_nm = 1;
                        return true;
                    }
                }
            
                if (!\App\Helpers\Setting::value('google_tokens:' . $this->api->id)) {
                    if (\App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id)) {
                        if ($app_id === \App\Helpers\Setting::value('app_encryption_app_id_2_2:' . $this->api->id)) {
                            $this->auth_app_id_nm = 2;
                            return true;
                        }
                    } else {
                        if ($app_id === \App\Helpers\Setting::value('app_id_2:' . $this->api->id) || 
                            $app_id === \App\Helpers\Setting::value('app_encryption_app_id_2_2:' . $this->api->id)) {
                            $this->auth_app_id_nm = 2;
                            return true;
                        }
                    }
                }
                
                if (\App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id)) {
                    if ($app_id === \App\Helpers\Setting::value('app_encryption_app_id_3:' . $this->api->id)) {
                        $this->auth_app_id_nm = 3;
                        return true;
                    }
                } else {
                    if ($app_id === \App\Helpers\Setting::value('app_id_3:' . $this->api->id) || 
                        $app_id === \App\Helpers\Setting::value('app_encryption_app_id_3:' . $this->api->id)) {
                        $this->auth_app_id_nm = 3;
                        return true;
                    }
                }
                
                Helper::log("authHeader Authorisation Error");
                
                abort(404);
            }
        }
    }
}
