<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;
use App\Models\Server;
use App\Models\ApiServer;
use Illuminate\Http\Request;
use App\Models\Api;
use App\Helpers\Helper;
use Storage;
use Carbon\Carbon;
use File;
use Response;
use DB;
use App\Helpers\Setting;

class SettingController extends Controller
{
    protected $api = null;

    public function __construct(Request $request, Server $server)
    {
        parent::__construct($request);

        $this->server = $server;
    }

    public function index()
    {
        return $this->view('setting.index');
    }

    public function serversSettings()
    {
        return $this->view('setting.servers');
    }

    public function serversSettingsSave(Request $request)
    {
        \App\Helpers\Setting::value('minute_not_available', $request->
            minute_not_available);
        \App\Helpers\Setting::value('app_encryption_setting', $request->
            app_encryption_setting ? true : false);

        \App\Helpers\Helper::$apiid = 0;

        foreach (Api::get() as $item) {
            \App\Helpers\Helper::$apiid = $item->id;

            \App\Helpers\Setting::value(
                'app_encryption_app_id:' . $item->id, 
                \App\Helpers\Helper:: crypt(
                    \App\Helpers\Setting::value('app_id:' . $item->id), 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->id).':' . $item->id)
                )
            );

            \App\Helpers\Setting::value(
                'app_encryption_app_id_2:' . $item->id, 
                \App\Helpers\Helper::crypt(
                    \App\Helpers\Setting::value('app_id_2:' . $item->id), 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->id), 
                    \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $item->id)
                )
            );

            \App\Helpers\Setting::value(
                'app_encryption_app_id_2_2:' . $item->id, 
                \App\Helpers\Helper::crypt(
                    \App\Helpers\Setting::value('app_id_2:' . $item->id), 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->id), 
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->id).'_2:' . $item->id)
                )
            );
        }

        \App\Helpers\Helper::$apiid = 0;

        foreach (ApiServer::select('api_servers.*', 'servers.ip')->join('servers',
            'servers.id', '=', 'api_servers.server_id')->get() as $item) {
            \App\Helpers\Helper::$apiid = $item->api_id;

            if ($item->ip) {
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $item->api_id)
                    ), 
                ]);
                    
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip_2' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).':' . $item->api_id)
                    ), 
                ]);
                    
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip_3' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).'_2:'.$item->api_id)
                    ), 
                ]);
            }
        }

        \App\Helpers\Helper::$apiid = 0;

        \App\Helpers\Setting::value('time_connection_error', $request->
            time_connection_error);

        return Redirect::route('servers.setting', []);
    }

    public function minuteNotAvailableSave(Request $request)
    {
        \App\Helpers\Setting::value($request->key, $request->minute_not_available);
    }

    public function save(Request $request)
    {
        \App\Helpers\Setting::value('exit_ad-api:' . $this->api->id, $request->
            exit_ad ? true : false);
        \App\Helpers\Setting::value('sl_ad-api:' . $this->api->id, $request->
            sl_ad ? true : false);
        \App\Helpers\Setting::value('con_ad-api:' . $this->api->id, $request->
            con_ad ? true : false);
        \App\Helpers\Setting::value('strt_ad-api:' . $this->api->id, $request->
            {"strt_ad"} ? true : false);
        
        \App\Helpers\Setting::value('app_api_auth:' . $this->api->id, $request->
            app_api_auth ? true : false);
        \App\Helpers\Setting::value('google_tokens:' . $this->api->id, $request->
            google_tokens ? true : false);
        \App\Helpers\Setting::value('app_id:' . $this->api->id, $request->app_id);
        \App\Helpers\Setting::value('app_id_2:' . $this->api->id, $request->app_id_2);
        \App\Helpers\Setting::value('app_id_3:' . $this->api->id, $request->app_id_3);
        \App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id,
            $request->app_api_auth_encryption_app_id ? true : false);
        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->api->id,
            $request->app_api_auth_encryption_method);
        \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $this->api->id, $request->
            app_api_auth_encryption_key);
            
            
        \App\Helpers\Setting::value('app_api_auth_encryption_key_1:' . $this->api->id, $request->
            app_api_auth_encryption_key_1);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_2);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_3:' . $this->api->id, $request->
            app_api_auth_encryption_key_3);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_4:' . $this->api->id, $request->
            app_api_auth_encryption_key_4);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_5:' . $this->api->id, $request->
            app_api_auth_encryption_key_5);
            
        \App\Helpers\Setting::value('app_api_auth_encryption_key_1_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_1_2);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_2_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_2_2);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_3_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_3_2);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_4_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_4_2);
        \App\Helpers\Setting::value('app_api_auth_encryption_key_5_2:' . $this->api->id, $request->
            app_api_auth_encryption_key_5_2);
            
            
        \App\Helpers\Setting::value('app_encryption_setting_1:' . $this->api->id, $request->
            app_encryption_setting_1);
        \App\Helpers\Setting::value('app_encryption_setting_2:' . $this->api->id, $request->
            app_encryption_setting_2);
        \App\Helpers\Setting::value('app_encryption_setting_3:' . $this->api->id, $request->
            app_encryption_setting_3);
        \App\Helpers\Setting::value('app_encryption_setting_4:' . $this->api->id, $request->
            app_encryption_setting_4);
        \App\Helpers\Setting::value('app_encryption_setting_5:' . $this->api->id, $request->
            app_encryption_setting_5);

        \App\Helpers\Helper::$apiid = $this->api->id;

        \App\Helpers\Setting::value(
            'app_encryption_app_id:' . $this->api->id, 
            \App\Helpers\Helper:: crypt(
                \App\Helpers\Setting::value('app_id:' . $this->api->id), 
                \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->api->id),
                \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).':' . $this->api->id)
            )
        );
        
        \App\Helpers\Setting::value(
            'app_encryption_app_id_2:' . $this->api->id, 
            \App\Helpers\Helper::crypt(
                 \App\Helpers\Setting::value('app_id_2:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $this->api->id)
            )
        );
        
        \App\Helpers\Setting::value(
            'app_encryption_app_id_2_2:' . $this->api->id, 
            \App\Helpers\Helper::crypt(
                 \App\Helpers\Setting::value('app_id_2:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).'_2:' . $this->api->id)
            )
        );
        
        \App\Helpers\Setting::value(
            'app_encryption_app_id_3:' . $this->api->id, 
            \App\Helpers\Helper::crypt(
                 \App\Helpers\Setting::value('app_id_3:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->api->id), 
                 \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).'_2:' . $this->api->id)
            )
        );
        
        \App\Helpers\Setting::value('conf_e-api:' . $this->api->id, $request->
            conf_e ? true : false);
        \App\Helpers\Setting::value('crypto_config_no_1-api:' . $this->api->id, $request->
            crypto_config_no_1 ? true : false);
        \App\Helpers\Setting::value('strict_u-api:' . $this->api->id, $request->
            strict_u ? true : false);

        \App\Helpers\Setting::value('min_version:' . $this->api->id, $request->
            min_version);
        \App\Helpers\Setting::value('connect_ads-api:' . $this->api->id, $request->
            connect_ads ? true : false);
        \App\Helpers\Setting::value('try_pro_always_on_startup-api:' . $this->api->id, $request->
            try_pro_always_on_startup ? true : false);
        \App\Helpers\Setting::value('connect_ads_day-api:' . $this->api->id, intval($request->
            connect_ads_day));
        \App\Helpers\Setting::value('list_up_when_start-api:' . $this->api->id, $request->
            list_up_when_start ? true : false);
        \App\Helpers\Setting::value('ads_p-api:' . $this->api->id, intval($request->
            ads_p));
        \App\Helpers\Setting::value('start_ads-api:' . $this->api->id, intval($request->
            start_ads));
        \App\Helpers\Setting::value('start_server_rate-api:' . $this->api->id, $request->
            start_server_rate ? true : false);
        if ($request->start_server_rate) {
            DB::table('api_servers')->where('api_id', $this->api->id)->update(['status_rating' =>
                1]);
        } else {
            DB::table('api_servers')->where('api_id', $this->api->id)->update(['status_rating' =>
                0]);
        }
        \App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id, $request->
            pro_for_pro ? true : false);

        \App\Helpers\Setting::value('day_try_pro:' . $this->api->id, $request->
            day_try_pro);

        \App\Helpers\Helper::$apiid = $this->api->id;

        foreach (ApiServer::select('api_servers.*', 'servers.ip')->join('servers',
            'servers.id', '=', 'api_servers.server_id')->where('api_servers.api_id', '=', $this->
            api->id)->get() as $item) {
            \App\Helpers\Helper::$apiid = $item->api_id;

            if ($item->ip) {
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $item->api_id)
                    ), 
                ]);
                    
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip_2' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).':' . $item->api_id)
                    ), 
                ]);
                    
                DB::table('api_servers')->where('id', '=', $item->id)->update([
                    'encrypt_ip_3' => \App\Helpers\Helper::crypt(
                        $item->ip, 
                        \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $item->api_id), 
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).'_2:'.$item->api_id)
                    ), 
                ]);
            }
        }

        \App\Helpers\Helper::$apiid = $this->api->id;

        return Redirect::route('apis.setting.index', ['api_id' => $this->api->id]);
    }

    public function apiGet(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);
        
        $obj = new \StdClass;
        $obj->google = \App\Helpers\Setting::value('google_tokens:' . $this->api->id) ? 1 : 0;
        $obj->min_version = \App\Helpers\Setting::value('min_version:' . $this->api->id);
        $obj->connect_ads = \App\Helpers\Setting::value('connect_ads-api:' . $this->api->
            id) ? 1 : 0;
        $obj->connect_ads_day = \App\Helpers\Setting::value('connect_ads_day-api:' . $this->
            api->id);
        $obj->try_pro_always_on_startup = \App\Helpers\Setting::value('try_pro_always_on_startup-api:' .
            $this->api->id) ? 1 : 0;
        $obj->start_server_pro = null;
        $obj->start_server_free = null;
        $obj->local_ip_pro = null;
        $obj->list_up_when_start = \App\Helpers\Setting::value('list_up_when_start-api:' .
            $this->api->id) ? 1 : 0;
        $obj->pro_for_pro = \App\Helpers\Setting::value('pro_for_pro-api:' .
            $this->api->id) ? 1 : 0;
            
            
            
        $obj->{"disc-ad"} = \App\Helpers\Setting::value('exit_ad-api:' .
            $this->api->id) ? 1 : 0;
        $obj->{"sl-ad"} = \App\Helpers\Setting::value('sl_ad-api:' .
            $this->api->id) ? 1 : 0;
        $obj->{"con-ad"} = \App\Helpers\Setting::value('con_ad-api:' .
            $this->api->id) ? 1 : 0;
        $obj->{"strt-ad"} = \App\Helpers\Setting::value('strt_ad-api:' .
            $this->api->id) ? 1 : 0;
            
            
        $obj->conf_e = \App\Helpers\Setting::value('conf_e-api:' .
            $this->api->id) ? 1 : 0;
        $obj->strict_u = \App\Helpers\Setting::value('strict_u-api:' .
            $this->api->id) ? 1 : 0;
        $obj->ads_p = \App\Helpers\Setting::value('ads_p-api:' . $this->api->id);
        $obj->{"start-ads"} = \App\Helpers\Setting::value('start_ads-api:' . $this->api->id);
        $obj->method = \App\Helpers\Setting::value('app_api_auth_encryption_method:' . $this->
            api->id);
        //$obj->key = \App\Helpers\Setting::value('app_api_auth_encryption_key:' . $this->api->id);
        $obj->day_try_pro = \App\Helpers\Setting::value('day_try_pro:' . $this->api->id);

        $status_fake = 0;
        
        if ($this->app_encryption_nm == 3) {
            //$status_fake = 1;
        }

        if (\App\Helpers\Setting::value('start_server_rate-api:' . $this->api->id)) {
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 1)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_free = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 1)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $local_ip_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_vpn',
                '=', 1)->where('servers.status', '=', 1)->where('api_servers.status_fake', '=',
                1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_fake_ip = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('servers.status', '=', 1)->
                where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_local = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 1)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_free = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 1)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_local_ip_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_ss',
                '=', 1)->where('servers.status', '=', 1)->where('api_servers.status_fake', '=',
                1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_fake_ip = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_not_rating',
                '=', 0)->where('api_servers.status_pro', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('servers.status', '=', 1)->
                where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.rate', 'DESC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_local = $query->first();
        } else {
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 1)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 0)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_free = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 1)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $local_ip_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_vpn',
                '=', 1)->where('servers.status', '=', 1)->where('api_servers.status_fake', '=',
                1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_fake_ip = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 0)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_vpn', '=', 1)->where('servers.status', '=', 1)->
                where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $server_local = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 1)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 0)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 0)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_free = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 1)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('api_servers.api_id', '=',
                $this->api->id)->where('servers.status', '=', 1)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_local_ip_pro = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.api_id',
                '=', $this->api->id)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_ss',
                '=', 1)->where('servers.status', '=', 1)->where('api_servers.status_fake', '=',
                1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_fake_ip = $query->first();
            $query = Server::select('servers.*')->join('api_servers',
                'api_servers.server_id', '=', 'servers.id')->where('api_servers.api_id', '=', $this->
                api->id)->where('api_servers.status_api', '=', 1)->where('api_servers.status_pro',
                '=', 0)->where('api_servers.status_not_rating', '=', 0)->where('api_servers.status_fake',
                '=', $status_fake)->where('api_servers.status_ss', '=', 1)->where('servers.status', '=', 1)->
                where('api_servers.api_id', '=', $this->api->id)->where('api_servers.status_local',
                '=', 1)->orderBy('servers.count_connections', 'ASC');
            $query->whereRaw(("(`servers`.`max_connection_rating` = 0 or `servers`.`count_connections` <= `servers`.`max_connection_rating`)"));
            $ss_server_local = $query->first();
        }

        if ($server_pro) {
            if (!\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id) || (\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id) && $this->prokey)) {
                $obj->start_server_pro = $server_pro->ip;
            }
        }
        if ($server_free) {
            $obj->start_server_free = $server_free->ip;
        }
        if ($local_ip_pro) {
            $obj->local_ip_pro = $local_ip_pro->ip;
        }
        if ($server_fake_ip) {
            $obj->fip = $server_fake_ip->ip;
        }
        if ($server_local) {
            $obj->{'local-ip'} = $server_local->ip;
        }

        if ($ss_server_pro) {
            $obj->{'ss_start_server_pro'} = $ss_server_pro->ip;
        }
        if ($ss_server_free) {
            $obj->{'ss_start_server_free'} = $ss_server_free->ip;
        }
        if ($ss_local_ip_pro) {
            $obj->{'ss_local_ip_pro'} = $ss_local_ip_pro->ip;
        }
        if ($ss_server_fake_ip) {
            $obj->{'ss_fip'} = $ss_server_fake_ip->ip;
        }
        if ($ss_server_local) {
            $obj->{'ss_local_ip'} = $ss_server_local->ip;
        }

        return Response::json($obj, 200, [], JSON_HEX_TAG);
    }
}
