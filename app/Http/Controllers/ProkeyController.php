<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;
use App\Models\Server;

use App\Models\Prokey;
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

class ProkeyController extends Controller
{
    public function __construct(Request $request, Server $server)
    {
        parent::__construct($request); 

        $this->server = $server;
    }
    
    public function index(Request $request)
    {
        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'id';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = Prokey::select('prokeys.*');

        $query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' :
            'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'ip':
                    case 'name':
                        $query->where($key, 'like', '%' . urldecode($value) . '%');

                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }
        
        $this->params['google_token'] = Helper::getGoogleTokenInfo();

        $this->params['google_oauth2_url'] = env('OAuthGoogleUrl'). '?' . urldecode(http_build_query([
            'redirect_uri'  => route('auth2.google.token'),
            'response_type' => 'code',
            'access_type' => 'offline',
            'client_id'     => env('OAuthGoogleId'),
            'scope'         => implode(' ', ['https://www.googleapis.com/auth/androidpublisher'])]));

        $this->params['items'] = $query->paginate(800);
        $this->params['countall'] = $query->count();

        return $this->view('prokeys.index');
    }

    public function item(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Prokey::where('id', '=', $request->id)->firstOrFail();
        }

        return $this->view('prokeys.item');
    }

    public function save(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Prokey::where('id', '=', $request->id)->firstOrFail();
        } else {
            $this->params['item'] = new Prokey;
        }

        $dataitem = request('dataitem');

        if (!is_array($dataitem)) {
            $dataitem = [];
        }

        $rules = [
        ];

        if ($this->params['item']->id) {
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

            $this->params['item']->save();

            return Redirect::route('prokeys.index', []);
        }

        if ($this->params['item']->id) {
            return Redirect::route('prokeys.edit', ['id' => $this->params['item']->id])->
                withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('prokeys.add', [])->withInput()->withErrors($validation)->
                with('message', 'Некоторые поля заполнены не верно.');
        }
    }

    public function delete(Request $request)
    {
        $this->params['item'] = Prokey::where('id', '=', $request->id)->firstOrFail();
        $this->params['item']->delete();
        return Redirect::route('prokeys.index');
    }
    
    public function settings(Request $request)
    {
        return $this->view('prokeys.setting');
    }

    public function settingsSave(Request $request)
    {
        \App\Helpers\Setting::value('get_pro_limit_24h', abs(intval($request->get_pro_limit_24h)));
        
        \App\Helpers\Setting::value('pro_id_limit_1h', abs(intval($request->pro_id_limit_1h)));
        
        \App\Helpers\Setting::value('pro_id_limit_24h', abs(intval($request->pro_id_limit_24h)));
        
        return Redirect::route('prokeys.settings', []);
    }

    public function apiGetPro(Request $request)
    {
        $code = 200;
        
        $obj = new \StdClass;
        
        $this->ctrIp($request);
        
        $ip = Ip::where("ip", "=", $_SERVER['REMOTE_ADDR'])->first();
        
        if (!$ip) {
            $ip = new Ip;
                
            $ip->ip = $_SERVER['REMOTE_ADDR'];
        }
        
        if (!$ip->pro_id_limit_24h_at) {
            $ip->pro_id_limit_24h_at = date("Y-m-d H:i:s");
            $ip->pro_id_count_24h = 0;
        }
        
        if (date_create($ip->pro_id_limit_24h_at)->getTimestamp() < time() - 60 * 60 * 24) {
            $ip->pro_id_limit_24h_at = date("Y-m-d H:i:s");
            $ip->pro_id_count_24h = 0;
        }
        
        $ip->pro_id_count_24h = $ip->pro_id_count_24h + 1;
        
        $ip->save();
        
        if ($ip->pro_id_count_24h > \App\Helpers\Setting::value('get_pro_limit_24h')) {
            $Badip = Badip::where("ip", "=", $_SERVER['REMOTE_ADDR'])->first();
            
            if (!$Badip) {
                $Badip = new Badip;
                
                $Badip->ip = $_SERVER['REMOTE_ADDR'];
                $Badip->status = 1;
                
                $Badip->saveDB();                                        
            }
            
            http_response_code(500);
            echo json_encode([
                'status' => 0,
                'error' => 1006, // 24h retry limit
            ], JSON_HEX_TAG);
            exit;
        } else {
            $prokey = Prokey::getProkeyByPurchase($request->packageName, $request->subscriptionId, $request->token);
            
            if ($prokey && $prokey->isAtive()) {
                $obj->{"pro-id"} = $prokey->getProId();
            } else {
                $code = 403;
                
                $obj->{"error"} = "token subscription not valid";
            }
        }
        
        return Response::json($obj, $code, [], JSON_HEX_TAG);
    }

    public function apiCheckPro(Request $request)
    {
        $obj = new \StdClass;
        
        $code = 200;
        
        $obj->status = 0;
        
        $this->ctrIp($request);
        
        $proId = null;
        
        if ($request->{"pro-id"}) {
            $proId = $request->{"pro-id"};
        } elseif (array_get(getallheaders(), 'pro-id')) {
            $proId = array_get(getallheaders(), 'pro-id');
        }
        
        if ($proId) {
            $prokey = Prokey::where("pro_id", "=", $proId)->first();
            
            if ($prokey && $prokey->isAtive()) {
                $obj->status = 1;
            } else {
                $code = 403;
                
                $obj->{"error"} = "pro-id not found";
            }
        }
        
        return Response::json($obj, $code, [], JSON_HEX_TAG);
    }

    public function statusUpdate(Request $request)
    {
        $this->params['item'] = Prokey::where('id', '=', $request->id)->firstOrFail();

        $this->params['item']->status = $request->status;

        $this->params['item']->save();

        return Response::json(['status' => 1]);
    }

    public function check(Request $request)
    {
        set_time_limit(3600);
        
        Helper::getGoogleToken();
        
        Prokey::where('last_check_time_at', '<', date("Y-m-d H:i:s", time() - 60 * 60 * 24))->chunk(500, function ($items) {
            foreach ($items as $item) {
                Prokey::getProkeyByPurchase($item->package_name, $item->subscription_id, $item->token, $item);
            }
        });
    }
}
