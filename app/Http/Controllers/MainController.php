<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;
use App\Models\Server;
use App\Models\Badip;
use App\Models\Googletoken;
use App\Models\Prokey;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use Storage;
use Carbon\Carbon;
use File;
use Response;
use DB;

use Google\Client;
use Google\Service\PlayIntegrity;
use Google\Service\PlayIntegrity\DecodeIntegrityTokenRequest;

class MainController extends Controller
{
    public function index()
    {
        return $this->view('main.index');
    }

    public function test()
    {
        echo "<pre>\r\n";
        $token = "eyJhbGciOiJBMjU2S1ciLCJlbmMiOiJBMjU2R0NNIn0.U8LDlhzV16zvXgwlpbXk5ZgPN1gBW-dMrWZz0ZZeHruYzKluC_9SHQ.JhZ7NhV4tJ6nX957.GWxIWAF4iKSC3fPC6pY4rQCv4ObZ6yRh5jBqT_Ila3da_uzvzcNY55gA97vo1ILXc9fwFqT-1ZI8CVnwkk32GDOhTvnwEyOCx5FbD13zreJ9jtDVjenCIj_gZYEoEJS86cTHs_MoqxNiz5q_0sbh1Vuq3fVFION5dMjl80S6nEMd_Lt2h7WwTKcQ9fKTskc57DX3VNIydilX1awYE-QCOauT8KihlMlPk1830KUvgzmL9WT1QjwzVdutZ5b1wulyxpvrkufF4j_Y2ru7oPvMo0MRCxm6nU8RmzdW3PoWZw83rY7gbExFgrRiuzd_c29XtMl-X_EiIjTpDQ9dGFA6wEG9HPMaTzAsFNCuGsi_ThJI8dEcSdSmn3mrxx2awetFfOmNGDEaXMDqwSQzkvxZgvDO2N8DNKxBsrYW1yVCSe1e--OIQZb0GG7g5h9qOpfYTsEAVLd_ZHmwrJaRVoIFjAbaOjwWFvsvPxBVdOmCan12NIXrTAkUOYjoAytKrjTuzv7bT0XQpwGrDFsfR6e4qLfGCSTPM5scWOTInZ08TlHueex7B_ZJBNHONkX7tpflTJNeHlpccwQjgfZIOoxa8_2-xeyfToR6UFatwO35Swb_AzH-jJoL5gbOEzn64nVneKf6gKPOwgRzRU0S7xqzBgWDHqDAzN02IsV1nqGdm6efxnpC4ceThjwXcPy_QvzuYYsnPMggsVM3hPYM1ClIjhJMxuZ01xxkFnKY-tEr-OIPGjsWTP--WHAMfIYDVJq_xSRVzQ5ViKNKAcqQS3m3zCTeGF4Z6suVIKeJEUJrHGyC3YHVA4E_3yxP-ruOkI9BhESB97N7z0hyV98K6fDVUq9ICBLGkd5d636UL1otG3BqPHa9zOHEtc8h8hxfygSzlvoULeqvPntHwwbF34pEzh3MrrcFRGFojQNPe4aHJ6XSdyRFwKtAirFpk-CJ8VA_CGeAd-SO2K3nGP7PK9ab4_Gj-gYZosi9Wb1eIIrCOwn30dfLOQstbr_GZiI-cDKK_l3RdXBpS2oWPP1jA2b8LJ8iJdK-qEhV4VI9krN6E6AMkBEDGIZED1NE0qpV9Jis4Gf5Ifu9CJDRzdeJilABVop2UnG0JaAA-HnHPyIYAHZPu3XAm0_PnsrNuJGsBNsH7kYWNEwYCJCFX2-rnsHseVoxODU7fXUQaKPZZYGfho_yrrvS10g.q7nTaazoFLhjAnWIJHHf3w";

        try {
            $client = new Client();
            $client->setAuthConfig(base_path("config/google.json"));
            $client->addScope(PlayIntegrity::PLAYINTEGRITY);
            $service = new PlayIntegrity($client);
            $tokenRequest = new DecodeIntegrityTokenRequest();
            $tokenRequest->setIntegrityToken($token);
            $result = $service->v1->decodeIntegrityToken('vpn.russia_tap2free', $tokenRequest);

            //$deviceVerdict = $result->deviceIntegrity->deviceRecognitionVerdict;
            //$appVerdict = $result->appIntegrity->appRecognitionVerdict;
            //$accountVerdict = $result->accountDetails->appLicensingVerdict;

            $deviceVerdict = $result->getTokenPayloadExternal()->getDeviceIntegrity()->
                deviceRecognitionVerdict;
            $appVerdict = $result->getTokenPayloadExternal()->getAppIntegrity()->
                appRecognitionVerdict;
            $accountVerdict = $result->getTokenPayloadExternal()->getAccountDetails()->
                appLicensingVerdict;

            //Possible values of $deviceVerdict[0] : MEETS_BASIC_INTEGRITY, MEETS_DEVICE_INTEGRITY, MEETS_STRONG_INTEGRITY
            if (!isset($deviceVerdict) || $deviceVerdict[0] !== 'MEETS_DEVICE_INTEGRITY') {
                echo "device doesn't meet requirement";
                exit(1);
            }

            //Possible values of $appVerdict: PLAY_RECOGNIZED, UNRECOGNIZED_VERSION, UNEVALUATED
            if ($appVerdict !== 'PLAY_RECOGNIZED') {
                echo "App not recognized";
                exit(1);
            }

            //Possible values of $accountVerdict: LICENSED, UNLICENSED, UNEVALUATED
            if ($accountVerdict !== 'LICENSED') {
                echo "User is not licensed to use app";
                exit(1);
            }

            echo "deviceVerdict[0]: " . $deviceVerdict[0] . "\r\n";
            echo "appVerdict: " . $appVerdict . "\r\n";
            echo "accountVerdict: " . $accountVerdict . "\r\n";
            echo "date: " . date("Y-m-d H:i:s", $result->getTokenPayloadExternal()->getRequestDetails()->timestampMillis / 1000) . "\r\n";
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function badip(Request $request)
    {
        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'id';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = Badip::select('badips.*');

        $query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' :
            'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                    case 'server':
                    case 'ip':
                        $query->where($key, 'like', '%' . urldecode($value) . '%');

                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $this->params['items'] = $query->paginate(800);

        return $this->view('badip.index');
    }

    public function badipitem(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Badip::where('id', '=', $request->id)->firstOrFail();
        }

        return $this->view('badip.item');
    }

    public function googletoken(Request $request)
    {
        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'id';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = Googletoken::select('googletokens.*');
        
        $this->params['countall'] = $query->count();
        
        $query->where('api_id', '=', $this->api->id);
        
        $this->params['countthis'] = $query->count();

        $query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' :
            'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                    case 'server':
                    case 'token':
                        $query->where($key, 'like', '%' . urldecode($value) . '%');

                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $this->params['items'] = $query->paginate(100);

        return $this->view('googletoken.index');
    }

    public function changeStatus(Request $request)
    {
        $Badip = Badip::where("id", "=", $request->id)->firstOrFail();

        $Badip->status = $request->status;

        $Badip->saveDB();

        return 1;
    }

    public function changeStatusGoogleToken(Request $request)
    {
        $Googletoken = Googletoken::where("id", "=", $request->id)->firstOrFail();

        $Googletoken->status = $request->status;

        $Googletoken->save();

        return 1;
    }

    public function badipSync(Request $request)
    {
        foreach (config("db") as $db_key => $db_config) {
            if (isset($db_config["badip_save"]) && $db_config["badip_save"]) {
                DB::connection($db_key)->table('badips')->TRUNCATE();
            }
        }

        $fp = @fopen(config("app.BADIP_FILE"), "r");
        if ($fp) {
            $inserts = [];

            //$inserts[] = ["ip" => "176.98.25.153", "status" => "1", "created_at" => date("Y-m-d H:i:s")];

            while (($buffer = fgets($fp, 4096)) !== false) {
                $inserts[] = ["ip" => trim($buffer), "status" => "1", "created_at" => date("Y-m-d H:i:s")];

                if (count($inserts) > 100) {
                    foreach (config("db") as $db_key => $db_config) {
                        if (isset($db_config["badip_save"]) && $db_config["badip_save"]) {
                            DB::connection($db_key)->table('badips')->insert($inserts);
                        }
                    }
                    
                    $inserts = [];
                }
            }

            if (count($inserts) > 0) {
                foreach (config("db") as $db_key => $db_config) {
                    if (isset($db_config["badip_save"]) && $db_config["badip_save"]) {
                        DB::connection($db_key)->table('badips')->insert($inserts);
                    }
                }
            }

            if (!feof($fp)) {
            }
            fclose($fp);
        }

        return 1;
    }

    public function googletokenClear()
    {
        Googletoken::where('created_at', '<', date("Y-m-d H:i", time() - 3600*24))
            ->orWhere('time', '<', time() - 3600*24)
            ->delete();
            
        $this->autoSwitchOnDate();
        $this->clearOnDate();
    }

    public function ipClear()
    {
        foreach (config("db") as $db_key => $db_config) {
            if (isset($db_config["token_save"]) && $db_config["token_save"]) {
                DB::connection($db_key)->table('ips')
                    ->where('first_error_short_code_at', '<', date("Y-m-d H:i", time() - 3600*24))
                    ->orWhereNull('first_error_short_code_at')
                    ->delete();
            }
        }
    }

    public function deleteGoogleToken(Request $request)
    {
        if ($request->selects && is_array($request->selects)) {
            foreach (Googletoken::whereIn("id", $request->selects)->get() as $googletoken) {
                $googletoken->delete();
            }
        }
        return 1;
    }
    
    public function badipsave(Request $request)
    {
        $this->params['item'] = null;

        if ($request->id) {
            $this->params['item'] = Badip::where('id', '=', $request->id)->firstOrFail();
        } else {
            $this->params['item'] = new Badip;
        }

        $dataitem = request('dataitem');

        if (!is_array($dataitem)) {
            $dataitem = [];
        }

        $rules = [
            "ip" => "required|ip",
        ];

        $validation = Validator::make($dataitem, $rules);

        if ($validation->passes()) {
            foreach ($dataitem as $key => $value) {
                switch ($key) {
                    default:
                        $this->params['item']->{$key} = $value;
                        break;
                }
            }
            
            if (!$this->params['item']->id && !isset($dataitem['status'])) {
                $this->params['item']->status = 1;
            }

            $this->params['item']->saveDB();

            return Redirect::route('badip.index', []);
        }

        if ($this->params['item']->id) {
            return Redirect::route('badip.item', ['id' => $this->params['item']->id])->
                withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('badip.item', [])->withInput()->withErrors($validation)->
                with('message', 'Некоторые поля заполнены не верно.');
        }
    }
    
    public function badipdelete(Request $request)
    {
        $this->params['item'] = Badip::where('id', '=', $request->id)->firstOrFail();
        
        $this->params['item']->delete();

        return Redirect::route('badip.index', []);
    }
    
    public function apiStatGet(Request $request)
    {
        $response = [];
        
        $response['count_all_servers'] = 0;
        $response['count_local_servers'] = 0;
        $response['count_world_servers'] = 0;
        
        $response['count_all_servers'] = Server::join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->distinct('servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('servers.status', '=', 1)
            ->count();
        
        $response['count_local_servers'] = Server::join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->distinct('servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('servers.status', '=', 1)
            ->where('api_servers.status_local', '=', 1)
            ->count();
        
        $response['count_world_servers'] = Server::join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->distinct('servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('servers.status', '=', 1)
            ->where('api_servers.status_local', '=', 0)
            ->count();
        
        return Response::json($response, 200, [], JSON_HEX_TAG);
    }
    
    public function apiStatAllGet(Request $request)
    {
        $response = [];
        
        $response['count_all_servers'] = 0;
        $response['count_countries'] = 0;
        $response['countries'] = [];
        
        $response['count_all_servers'] = Server::where('servers.status', '=', 1)
            ->count();
        
        Server::select("servers.*")
            ->distinct('servers.country')
            ->where('servers.status', '=', 1)
            ->orderBy('servers.country', 'asc')
            ->chunk(500, function ($servers) use (&$response) {
            foreach ($servers as $server) {
                if ($server->country) {
                    $is = false;
                    foreach ($response['countries'] as $counry) {
                        if ($counry["name"] == $server->country) {
                            $is = true;
                            break;
                        }
                    }
                    
                    if (!$is) {
                        $response['countries'][] = [
                            "name" => $server->country,
                            "flag" => asset('/images/' . $server->img_flag),
                        ];
                    }
                }
            }
        });
        
        $response['count_countries'] = count($response['countries']);
        
        return Response::json($response, 200, [], JSON_HEX_TAG);
    }

    public function autoSwitchOnDate()
    {
        //echo date("j");
        
        Server::where("auto_switch_on_date", "=", date("j"))->where("status", "=", "0")->update(["status" => 1]);
    }

    public function clearOnDate()
    {
        DB::statement("DELETE FROM `ips` 
            WHERE (`first_error_short_code_at` is null OR `first_error_short_code_at` < '".date("Y-m-d H:i:s", time() - 60 * 60 * 24)."') && 
                (`pro_id_limit_24h_at` is null OR `pro_id_limit_24h_at` < '".date("Y-m-d H:i:s", time() - 60 * 60 * 24)."')");
    }
}
