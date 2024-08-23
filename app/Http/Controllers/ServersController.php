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
use App\Models\ApiServer;
use Session;

class ServersController extends Controller
{
    protected $server;

    public function __construct(Request $request, Server $server)
    {
        parent::__construct($request);

        $this->server = $server;
    }

    public function copySelect(Request $request)
    {
        $servers = Server::whereIn('id', $request->servers_id)->get();

        foreach ($servers as $server) {
            $new_server = new Server();
            
            $newservername = $server->server;
            
            $new_server->server = $newservername;
            
            for ($i = 1; $i < 500 && DB::table('servers')->where('server', '=', $new_server->server)->count() > 0; $i++) {
                $new_server->server = $newservername . '_' . $i;
            }
            
            $new_server->name = $server->name;
            $new_server->country = $server->country;
            $new_server->img_flag = $server->img_flag;
            $new_server->img_map = $server->img_map;
            $new_server->ip = $server->ip;
            $new_server->type = $server->type;
            $new_server->ca_crt = $server->ca_crt;
            $new_server->client1_crt = $server->client1_crt;
            $new_server->client1_key = $server->client1_key;
            $new_server->date = $server->date;
            $new_server->created_at = $server->created_at;
            $new_server->updated_at = $server->updated_at;
            $new_server->config = $server->config;
            $new_server->count_url = $server->count_url;
            $new_server->count_connections = $server->count_connections;
            $new_server->status = $server->status;
            $new_server->local = $server->local;
            $new_server->fake_ip = $server->fake_ip;
            $new_server->config_ovpn = $server->config_ovpn;
            $new_server->udp_ca_crt = $server->udp_ca_crt;
            $new_server->udp_client1_crt = $server->udp_client1_crt;
            $new_server->udp_client1_key = $server->udp_client1_key;
            $new_server->udp_config = $server->udp_config;
            $new_server->udp_config_ovpn = $server->udp_config_ovpn;
            $new_server->ss_config = $server->ss_config;
            $new_server->count_url_ss = $server->count_url_ss;
            $new_server->count_connections_ss = $server->count_connections_ss;
            $new_server->sort = $server->sort + rand(1111,9999)/10000;
            $new_server->url_speed = $server->url_speed;
            $new_server->speed_mbps = $server->speed_mbps;
            $new_server->rate = $server->rate;
            $new_server->not_available_at = $server->not_available_at;
            $new_server->not_available_ss_at = $server->not_available_ss_at;
            $new_server->available_at = $server->available_at;
            
            if ($new_server->img_flag) {
                if (!file_exists(public_path() . '/images/' . $new_server->img_flag)) {
                    $new_server->img_flag = '';
                }
            }
            
            if ($new_server->img_map) {
                if (!file_exists(public_path() . '/images/' . $new_server->img_map)) {
                    $new_server->img_map = '';
                }
            }
            
            if ($new_server->img_flag) {
                $path_parts = pathinfo($new_server->img_flag);
                
                if (isset($path_parts['filename']) && isset($path_parts['extension'])) {
                    $filenew = str_random(20) . '.copy.' . $path_parts['extension'];
                
                    copy(public_path() . '/images/' . $new_server->img_flag, public_path() . '/images/' . $filenew);
                    
                    $new_server->img_flag = $filenew;
                }
            }
            
            if ($new_server->img_map) {
                $path_parts = pathinfo($new_server->img_map);
                
                if (isset($path_parts['filename']) && isset($path_parts['extension'])) {
                    $filenew = str_random(20) . '.copy.' . $path_parts['extension'];
                
                    copy(public_path() . '/images/' . $new_server->img_map, public_path() . '/images/' . $filenew);
                    
                    $new_server->img_map = $filenew;
                }
            }
            
            $new_server->save();
            
            foreach(ApiServer::select('api_servers.*')->where('api_servers.server_id', '=', $server->id)->get() as $item){
                $apiServer = new ApiServer;
               
                $apiServer->api_id = $item->api_id;
                $apiServer->server_id = $new_server->id;
               
                $apiServer->status_api = $item->status_api;
                $apiServer->status_ss = $item->status_ss;
                $apiServer->status_vpn = $item->status_vpn;
                $apiServer->status_pro = $item->status_pro;
                $apiServer->status_local = $item->status_local;
                $apiServer->status_fake = $item->status_fake;
                $apiServer->status_not_rating = $item->status_not_rating;
                $apiServer->status_rating = $item->status_rating;
               
                $apiServer->encrypt_ip = \App\Helpers\Helper::crypt(
                    $new_server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key:'.$item->api_id)
                );
                $apiServer->encrypt_ip_2 = \App\Helpers\Helper::crypt(
                    $new_server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).':'.$item->api_id)
                );
                $apiServer->encrypt_ip_3 = \App\Helpers\Helper::crypt(
                    $new_server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$item->api_id).'_2:'.$item->api_id)
                );
                
                $apiServer->save();
            } 
        }

        return Response::json(['status' => true]);
    }

    public function serverApisSave(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();
        
        $apis = Api::get();
        
        foreach ($apis as $api) {
            $apiServer = ApiServer::where('api_id', '=', $api->id)
                ->where('server_id', '=', $server->id)
                ->first();
            
            if (!$apiServer) {
                $apiServer = new ApiServer;
                
                $apiServer->api_id = $api->id;
                $apiServer->server_id = $server->id;
            }
            
            //$apiServer->status_api = request('apis.'.$api->id.'.status_api') ? 1 : 0;  
            $apiServer->status_ss = request('apis.'.$api->id.'.status_ss') ? 1 : 0; 
            $apiServer->status_vpn = request('apis.'.$api->id.'.status_vpn') ? 1 : 0; 
            $apiServer->status_pro = request('apis.'.$api->id.'.status_pro') ? 1 : 0; 
            $apiServer->status_local = request('apis.'.$api->id.'.status_local') ? 1 : 0; 
            $apiServer->status_fake = request('apis.'.$api->id.'.status_fake') ? 1 : 0; 
            $apiServer->status_not_rating = request('apis.'.$api->id.'.status_not_rating') ? 1 : 0;  
            
            $apiServer->save();
        }
        
        if ($this->api) {
            return Redirect::route('apis.servers.apis', ['api_id' => $this->api->id, 'server_id' => $server->id])->with('message', 'Сохранено !!!');
        } else {
            return Redirect::route('servers.apis', ['server_id' => $server->id])->with('message', 'Сохранено !!!');
        }
    }

    public function displace(Request $request)
    {
        $obj = null;
        
        if ($request->server_id) {
            $obj = Server::where('api_id', '=', $this->api->id)->where('id', '=', $request->server_id)->first();
        }
        
        if (!$obj) {
            $obj = Server::where('api_id', '=', $this->api->id)->orderBy('id', 'asc')->first();
        }
        
        if ($obj) {
            $servers = Server::where('api_id', '=', $this->api->id)
                ->where('id', '>=', $obj->id)
                ->orderBy('id', 'desc')
                ->get();
                
            $id_last = null;
            $id_new = null;
            
            foreach ($servers as $server) {
                if ($id_last) {
                    $id_new = $id_last;
                } else {
                    $id_new = $server->id + 1;
                }
                
                if (Server::where('id', '=', $id_new)->count() > 0) {
                    $id_new = (intval(Server::max('id')) + 1);
                }
                
                $id_last = $server->id;
                
                $server->id = $id_new;

                $server->save();
            }
            
            DB::statement("ALTER TABLE `servers` AUTO_INCREMENT=" . (intval(Server::max('id')) + 1));
        }
        
        return Redirect::route('apis.servers.index', ['api_id' => $this->api->id]);
    }

    public function servers(Request $request)
    {
        $this->params['apis'] = Api::get();

        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'sort';
        }

        $query = $this->server
            ->select('servers.*')
            ->leftJoin('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->select(
                'servers.*', 
                'api_servers.status_api',
                'api_servers.status_ss',
                'api_servers.status_vpn',
                'api_servers.status_pro',
                'api_servers.status_local',
                'api_servers.status_fake',
                'api_servers.status_not_rating',
                'api_servers.sort as api_servers_sort',
                'api_servers.id as api_server_id'
            )
            ->distinct('servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1);

        $querySt1 = clone $query;
        $querySt2 = clone $query;
        $querySt3 = clone $query;
        
        $querySt2->where("status", "=", 1);
        $querySt3->where("status", "=", 0);
        
        $this->params['count_all'] =  $querySt1->count();
        $this->params['count_active'] =  $querySt2->count();
        $this->params['count_noactive'] =  $querySt3->count();
        
        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        switch ($sort) {
            case 'sort':
                $sort = 'api_servers.sort';
                
                break;
            case 'not_available_at':
                $sort = DB::raw('IF(not_available_at, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(not_available_at), null)');
                
                break;
        }
        
        $query->orderBy($sort, stripos($this->params['sort'], '-')=== 0 ? 'desc' : 'asc');
        
        if (request('search.status') >= '0') {
            
        } else {
            $query->where('servers.status', '=', 1);
        }
        
        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);
                
                if (empty($value) && $value != '0') {
                    continue;
                }
                
                switch ($key) {
                    case 'status_ss_vpn':
                        if ($value == '1') {
                            $query->where('status_vpn', '=', 1);
                        }
                        if ($value == '2') {
                            $query->where('status_ss', '=', 1);
                        }
                        
                        break;
                    
                    case 'name':
                    case 'server':
                    case 'ip':
                        $query->where($key, 'like', '%'.urldecode($value).'%');
                    
                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $servers = $query->paginate(800);
        
        foreach ($servers as $server) {
            if ($server->not_available_at) {
                $server->minutes_not_available = round((time() - date_create($server->not_available_at)->getTimestamp())/60);
            } else {
                $server->minutes_not_available = '';
            }
            
            if ($server->not_available_ss_at) {
                $server->minutes_not_available_ss = round((time() - date_create($server->not_available_ss_at)->getTimestamp())/60);
            } else {
                $server->minutes_not_available_ss = '';
            }
        }

        $this->params['servers'] = $servers;

        return $this->view('servers.servers');
    }

    public function serverApis(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();
        
        $this->params['apis'] = Api::leftJoin('api_servers', function ($join) use ($server) {
            $join->on('api_servers.api_id', '=', 'apis.id')
                ->where('api_servers.server_id', '=', $server->id);
        })->select(
            'apis.*', 
            'api_servers.api_id', 
            'api_servers.server_id', 
            'api_servers.status_api', 
            'api_servers.status_ss', 
            'api_servers.status_vpn', 
            'api_servers.status_pro', 
            'api_servers.status_local', 
            'api_servers.status_fake', 
            'api_servers.status_not_rating', 

            'api_servers.encrypt_ip', 
            'api_servers.encrypt_ip_2', 
            'api_servers.encrypt_ip_3'
        )->get();
        
        $this->params['server'] = $server;
        
        return $this->view('servers.apis');
    }

    public function serversSave(Request $request)
    {
        if (is_array(request('servers'))) {
            foreach (request('servers') as $idserver) {
                if (request('chk_servers.'.$idserver)) {
                    DB::table('servers')
                        ->where('id', $idserver)
                        ->update(['status' => 1]);
                } else {
                    DB::table('servers')
                        ->where('id', $idserver)
                        ->update(['status' => 0]);
                }
            }
        }
        
        return Response::json(['status' => 1]);
    }

    public function index(Request $request)
    {
        $this->params['apis'] = Api::get();

        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'sort';
        }
        
        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = $this->server->select('servers.*');

        $querySt1 = clone $query;
        $querySt2 = clone $query;
        $querySt3 = clone $query;
        
        $querySt2->where("status", "=", 1);
        $querySt3->where("status", "=", 0);
        
        $this->params['count_all'] =  $querySt1->count();
        $this->params['count_active'] =  $querySt2->count();
        $this->params['count_noactive'] =  $querySt3->count();
        
        switch ($sort) {
            case 'not_available_at':
                $sort = DB::raw('IF(not_available_at, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(not_available_at), null)');
                
                break;
        }
        
        $query->orderBy($sort, stripos($this->params['sort'], '-')=== 0 ? 'desc' : 'asc');

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
                        $query->where($key, 'like', '%'.urldecode($value).'%');
                    
                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $servers = $query->paginate(800);
        
        foreach ($servers as $server) {
            if ($server->not_available_at) {
                $server->minutes_not_available = round((time() - date_create($server->not_available_at)->getTimestamp())/60);
            } else {
                $server->minutes_not_available = '';
            }
            
            if ($server->not_available_ss_at) {
                $server->minutes_not_available_ss = round((time() - date_create($server->not_available_ss_at)->getTimestamp())/60);
            } else {
                $server->minutes_not_available_ss = '';
            }
        }
            
        $this->params['servers'] = $servers;

        return $this->view('servers.index');
    }

    public function vpn(Request $request)
    {
        
    }

    public function ss(Request $request)
    {
        
    }

    public function add()
    {
        return $this->view('servers.add');
    }

    public function edit(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();

        $this->params['server'] = $server;

        return $this->view('servers.edit');
    }

    public function save(Request $request)
    {
        $input = $request->all();

        $validation = Validator::make($input, Server::$rules);

        if ($validation->passes()) {
            $data = [];

            if (isset($input['status']) && $input['status']) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }

            if (isset($input['server'])) {
                $data['server'] = $input['server'];
            }

            if (isset($input['country'])) {
                $data['country'] = $input['country'];
            }

            if (isset($input['img_flag_f']) && is_file(public_path() . '/tmp/' . $input['img_flag_f']) &&
                copy(public_path() . '/tmp/' . $input['img_flag_f'], public_path() . '/images/' .
                $input['img_flag_f'])) {
                public_path() . '/tmp/';
                $data['img_flag'] = $input['img_flag_f'];
            }

            if (isset($input['img_map_f']) && is_file(public_path() . '/tmp/' . $input['img_map_f']) &&
                copy(public_path() . '/tmp/' . $input['img_map_f'], public_path() . '/images/' .
                $input['img_map_f'])) {
                $data['img_map'] = $input['img_map_f'];
            }

            if (isset($input['name'])) {
                $data['name'] = $input['name'];
            }

            if (isset($input['ip'])) {
                $data['ip'] = $input['ip'];
            }

            if (isset($input['max_connection_rating'])) {
                $data['max_connection_rating'] = intval($input['max_connection_rating']);
            }

            if (isset($input['auto_switch_on_date']) && $input['auto_switch_on_date'] > 0) {
                $data['auto_switch_on_date'] = intval($input['auto_switch_on_date']);
            }

            if (isset($_FILES['files'])) {
                foreach ($_FILES['files']['name'] as $key => $value) {
                    switch ($value) {
                        case 'ca.crt':
                            $data['ca_crt'] = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $data['config_ovpn'] = null;

                            break;
                        case 'client1.crt':
                            $data['client1_crt'] = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $data['config_ovpn'] = null;

                            break;
                        case 'client1.key':
                            $data['client1_key'] = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $data['config_ovpn'] = null;

                            break;
                        case 'server-conf.txt':
                            $data['config'] = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $data['config_ovpn'] = null;

                            break;
                    }
                    
                }
                
                foreach ($_FILES['files']['name'] as $key => $value) {
                    $path_parts = pathinfo($_FILES['files']['name'][$key]);
                    
                    if(isset($path_parts['extension'])){
                        switch ($path_parts['extension']) {
                            case 'ovpn': 
                                $data['config_ovpn'] = file_get_contents($_FILES['files']['tmp_name'][$key]);
                                if(!empty($data['config_ovpn'])) {
                                }
                                
                                break;
                        }
                    } 
                }
            }
            
            if (isset($input['ss_config']) && strlen($input['ss_config']) > 5) {
                $data['ss_config'] = $input['ss_config'];
            } else {
                $data['ss_config'] = '';
            }

            if (isset($input['count_url'])) {
                $data['count_url'] = $input['count_url'];
            }

            if (isset($input['url_speed'])) {
                $data['url_speed'] = $input['url_speed'];
            }

            $server = $this->server->create($data);

            $server->save();
            
            if ($this->api) {
                $apiServer = ApiServer::where('api_id', '=', $this->api->id)
                    ->where('server_id', '=', $server->id)
                    ->first();
                
                if (!$apiServer) {
                    $apiServer = new ApiServer;
                
                    $apiServer->api_id = $this->api->id;
                    $apiServer->server_id = $server->id;
                }
            
                $apiServer->status_api = 1;
                
                $apiServer->save();
            }
            
            if (Session::get('select_server_id')) {
                $selectServer = Server::where('id', '=', Session::get('select_server_id'))->where('status', '=', '1')->first();
                
                if ($selectServer) {
                    $nextServer = Server::where('sort', '>', $selectServer->sort)->where('id', '!=', $selectServer->id)->where('status', '=', '1')->orderBy('sort', 'asc')->first();
                    
                    if ($nextServer) {
                        $server->sort = (floatval($selectServer->sort)+floatval($nextServer->sort))/2;
                    } else {
                        $server->sort = floatval($selectServer->sort) + 0.5;
                    }
                    
                    $server->save();
                }
                
                Session::forget('select_server_id');
            }
            
            $this->clearTmpPublicDir();

            if ($this->api) {
                return Redirect::route('apis.servers.servers', ['api_id' => $this->api->id]);
            } else {
                return Redirect::route('apis.servers', []);
            }
        }
        
        if ($this->api) {
            return Redirect::route('apis.servers.add', ['api_id' => $this->api->id])->
                 withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('servers.add', [])->
                 withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        }
    }

    private function clearTmpPublicDir()
    {
        if (file_exists(public_path() . '/tmp/') && is_dir(public_path() . '/tmp/')) {
            foreach (glob(public_path() . '/tmp/*') as $file) {
                unlink($file);
            }
        }
    }

    public function update(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();

        $input = $request->all();

        $rules = Server::$rules;

        $rules['server'] = $rules['server'] . ',' . $server->id;
        
        $rules["auto_switch_on_date"] = "numeric|between:1,28";
        
        $messages = [
            'auto_switch_on_date' => 'The Auto switch on date must be between 1 and 28.',
        ];

        $validation = Validator::make($input, $rules, $messages);

        if ($validation->passes()) {
            if (isset($input['server'])) {
                if (false && $server->server != $input['server']) {
                    if (Storage::disk('servers')->exists($server->server)) {
                        Storage::disk('servers')->deleteDirectory($server->server);
                    }

                    Storage::disk('servers')->makeDirectory($input['server']);
                }

                $server->server = $input['server'];
            }
            
            if (isset($input['status']) && $input['status']) {
                $server->status = 1;
            } else {
                $server->status = 0;
            }

            if (isset($input['server'])) {
                $server->server = $input['server'];
            }

            if (isset($input['country'])) {
                $server->country = $input['country'];
            }

            if (isset($input['img_flag_f']) && is_file(public_path() . '/tmp/' . $input['img_flag_f']) &&
                copy(public_path() . '/tmp/' . $input['img_flag_f'], public_path() . '/images/' .
                $input['img_flag_f'])) {
                public_path() . '/tmp/';
                $server->img_flag = $input['img_flag_f'];
            }

            if (isset($input['img_map_f']) && is_file(public_path() . '/tmp/' . $input['img_map_f']) &&
                copy(public_path() . '/tmp/' . $input['img_map_f'], public_path() . '/images/' .
                $input['img_map_f'])) {
                $server->img_map = $input['img_map_f'];
            }

            if (isset($input['name'])) {
                $server->name = $input['name'];
            }

            if (isset($input['ip'])) {
                $server->ip = $input['ip'];
            }

            if (isset($input['max_connection_rating'])) {
                $server->max_connection_rating = intval($input['max_connection_rating']);
            }

            if (isset($input['auto_switch_on_date']) && $input['auto_switch_on_date'] > 0) {
                $server->auto_switch_on_date = intval($input['auto_switch_on_date']);
            } else {
                $server->auto_switch_on_date = null;
            }

            if (isset($_FILES['files'])) {
                foreach ($_FILES['files']['name'] as $key => $value) {
                    switch ($value) {
                        case 'ca.crt':
                            $server->ca_crt = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $server->config_ovpn = null;

                            break;
                        case 'client1.crt':
                            $server->client1_crt = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $server->config_ovpn = null;

                            break;
                        case 'client1.key':
                            $server->client1_key = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $server->config_ovpn = null;

                            break;
                        case 'server-conf.txt':
                            $server->config = file_get_contents($_FILES['files']['tmp_name'][$key]);
                            $server->config_ovpn = null;

                            break;
                    }
                    
                }
                
                foreach ($_FILES['files']['name'] as $key => $value) {
                    $path_parts = pathinfo($_FILES['files']['name'][$key]);
                    
                    if(isset($path_parts['extension'])){
                        switch ($path_parts['extension']) {
                            case 'ovpn': 
                                $server->config_ovpn = file_get_contents($_FILES['files']['tmp_name'][$key]);
                                
                                break;
                        }
                    } 
                }
            }
            
            if (isset($input['ss_config']) && strlen($input['ss_config']) > 5) {
                $server->ss_config = $input['ss_config'];
            } else {
                $server->ss_config = '';
            }

            if (isset($input['count_url'])) {
                $server->count_url = $input['count_url'];
            }

            if (isset($input['url_speed'])) {
                $server->url_speed = $input['url_speed'];
            }           
            
            $server->save();

            $this->clearTmpPublicDir();

            if ($this->api) {
                return Redirect::route('apis.servers.servers', ['api_id' => $this->api->id]);
            } else {
                return Redirect::route('apis.servers', []);
            }
        }

        if ($this->api) {
            return Redirect::route('apis.servers.edit', ['server_id' => $server->id,
                'api_id' => $this->api->id])->withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        } else {
            return Redirect::route('servers.edit', ['server_id' => $server->id])->withInput()->withErrors($validation)->with('message',
                'Некоторые поля заполнены не верно.');
        }
    }

    public function destroyApi(Request $request)
    {
        $apiServer = ApiServer::where('api_id', '=', $this->api->id)
                ->where('server_id', '=', $request->server_id)
                ->first();
                
        if ($apiServer) {
            $apiServer->status_api = 0;
            $apiServer->status_ss = 0;
            $apiServer->status_vpn = 0;
            
            $apiServer->save();
        }
        
        return Redirect::route('apis.servers.servers', ['api_id' => $this->api->id]);
    }

    public function destroy(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();

        $server->delete();
        
        if (Storage::disk('servers')->exists($server->server)) {
            Storage::disk('servers')->deleteDirectory($server->server);
        }

        return Redirect::route('apis.servers');
    }

    public function upload_flag(Request $request)
    {
        $server = $this->server->find($request->id);

        $rules = ['img_flag' => 'required|image|mimes:jpeg,jpg,png,gif'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json(['message' => $validator->messages()->first('img_flag')]);
        }

        if ($server) {
            $dir = '/images/';

            $extension = 'jpg';

            $pathinfo = pathinfo($request->file('img_flag')->getClientOriginalName());

            if (isset($pathinfo['extension'])) {
                if ($pathinfo['extension'] == 'jpeg' || $pathinfo['extension'] == 'jpg' || $pathinfo['extension'] ==
                    'png' || $pathinfo['extension'] == 'gif') {
                    $extension = $pathinfo['extension'];

                } else {
                    $extension = 'txt';
                }
            }

            do {
                $filename = str_random(30) . '.' . $extension;
            } while (File::exists(public_path() . $dir . $filename));

            $request->file('img_flag')->move(public_path() . $dir, $filename);

            if ($server->img_flag && is_file(public_path() . $dir . $server->img_flag)) {
                unlink(public_path() . $dir . $server->img_flag);
            }

            $server->img_flag = $filename;
            $server->save();

            return Response::json(['id' => $server->id, 'name' => $filename, 'filelink' => $dir .
                $filename]);
        } else {
            $dir = '/tmp/';

            $extension = 'jpg';

            $pathinfo = pathinfo($request->file('img_flag')->getClientOriginalName());

            if (isset($pathinfo['extension'])) {
                if ($pathinfo['extension'] == 'jpeg' || $pathinfo['extension'] == 'jpg' || $pathinfo['extension'] ==
                    'png' || $pathinfo['extension'] == 'gif') {
                    $extension = $pathinfo['extension'];

                } else {
                    $extension = 'txt';
                }
            }

            do {
                $filename = str_random(30) . '.' . $extension;
            } while (File::exists(public_path() . $dir . $filename));

            $request->file('img_flag')->move(public_path() . $dir, $filename);

            return Response::json(['name' => $filename, 'filelink' => $dir . $filename]);
        }
    }

    public function upload_map(Request $request)
    {
        $server = $this->server->find($request->id);

        $rules = ['img_map' => 'required|image|mimes:jpeg,jpg,png,gif'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json(['message' => $validator->messages()->first('img_map')]);
        }

        if ($server) {
            $dir = '/images/';

            $extension = 'jpg';

            $pathinfo = pathinfo($request->file('img_map')->getClientOriginalName());

            if (isset($pathinfo['extension'])) {
                if ($pathinfo['extension'] == 'jpeg' || $pathinfo['extension'] == 'jpg' || $pathinfo['extension'] ==
                    'png' || $pathinfo['extension'] == 'gif') {
                    $extension = $pathinfo['extension'];

                } else {
                    $extension = 'txt';
                }
            }

            do {
                $filename = str_random(30) . '.' . $extension;
            } while (File::exists(public_path() . $dir . $filename));

            $request->file('img_map')->move(public_path() . $dir, $filename);

            if ($server->img_map && is_file(public_path() . $dir . $server->img_map)) {
                unlink(public_path() . $dir . $server->img_map);
            }

            $server->img_map = $filename;
            $server->save();

            return Response::json(['id' => $server->id, 'name' => $filename, 'filelink' => $dir .
                $filename]);
        } else {
            $dir = '/tmp/';

            $extension = 'jpg';

            $pathinfo = pathinfo($request->file('img_map')->getClientOriginalName());

            if (isset($pathinfo['extension'])) {
                if ($pathinfo['extension'] == 'jpeg' || $pathinfo['extension'] == 'jpg' || $pathinfo['extension'] ==
                    'png' || $pathinfo['extension'] == 'gif') {
                    $extension = $pathinfo['extension'];

                } else {
                    $extension = 'txt';
                }
            }

            do {
                $filename = str_random(30) . '.' . $extension;
            } while (File::exists(public_path() . $dir . $filename));

            $request->file('img_map')->move(public_path() . $dir, $filename);

            return Response::json(['name' => $filename, 'filelink' => $dir . $filename]);
        }
    }

    public function ajaxDeleteFlag(Request $request)
    {
        $server = $this->server->find($request->id);

        if ($server) {
            if ($server->img_flag && is_file(public_path() . '/images/' . $server->img_flag)) {
                unlink(public_path() . '/images/' . $server->img_flag);
            }

            $server->img_flag = '';
            $server->save();
        }

        return Response::json(['status' => 1]);
    }

    public function ajaxDeleteMap(Request $request)
    {
        $server = $this->server->find($request->id);

        if ($server) {
            if ($server->img_map && is_file(public_path() . '/images/' . $server->img_map)) {
                unlink(public_path() . '/images/' . $server->img_map);
            }

            $server->img_map = '';
            $server->save();
        }

        return Response::json(['status' => 1]);
    }

    public function update_crt_ss(Request $request)
    {
        $response = [];

        foreach (scandir(storage_path('ss-update_crt')) as $file) {
            if (preg_match("#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\.ss$#", $file, $match)) {
                $conf = trim(file_get_contents(storage_path('ss-update_crt') . "/" . $file));

                $response[] = [
                    "ip" => $match[1],
                    "conf" => $conf,
                ];

                if ($conf) {
                    DB::statement("UPDATE `servers` SET `ss_config` = :ss_config WHERE `servers`.`ip` = :ip;", [
                        'ss_config' => $conf,
                        'ip' => $match[1],
                    ]);
                    
                    /*
                    DB::statement("UPDATE `api_servers` 
                                   LEFT JOIN `servers` ON `api_servers`.`server_id` = `servers`.`id` 
                                   SET `api_servers`.`status_ss` = 1
                                   WHERE `servers`.`ip` = :ip;", [
                        'ip' => $match[1],
                    ]);
                    */

                    unlink(storage_path('ss-update_crt') . "/" . $file);
                }
            }
        }

        return Response::json(['status' => 1, 'response' => $response]);
    }

    public function update_crt(Request $request)
    {
        Server::distinct('ip')->chunk(100, function ($servers) {
            foreach ($servers as $server) {
                if(!Storage::disk('servers')->exists($server->ip)){
                    Storage::disk('servers')->makeDirectory($server->ip);
                }
            }
        });
        
        $response = [];
        
        foreach (Storage::disk('servers')->directories() as $directory) {
            if (preg_match("#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#", $directory)) {
                foreach (Storage::disk('servers')->files($directory) as $file) {
                    $pinf = pathinfo($file);
                    $res = Storage::disk('servers')->get($file);

                    switch ($pinf['basename']) {
                        case 'ca.crt':
                            $response['ips'][$directory] = $directory;
                    
                            $response['ca.crt'] = (isset($response['ca.crt']) ? $response['ca.crt'] : 0) + Server::where('ip', '=', $directory)->count();
                            
                            DB::statement("UPDATE `servers` SET `ca_crt` = :ca_crt WHERE `servers`.`ip` = :ip;", [
                                'ca_crt' => $res,
                                'ip' => $directory
                            ]);
                            DB::statement("UPDATE `servers` SET `config_ovpn` = :config_ovpn WHERE `servers`.`ip` = :ip;", [
                                'config_ovpn' => '',
                                'ip' => $directory
                            ]);
                            
                            Storage::disk('servers')->delete($file);

                            break;
                        case 'client1.crt':
                            $response['ips'][$directory] = $directory;
                    
                            $response['client1.crt'] = (isset($response['client1.crt']) ? $response['client1.crt'] : 0) + Server::where('ip', '=', $directory)->count();
                            
                            DB::statement("UPDATE `servers` SET `client1_crt` = :client1_crt WHERE `servers`.`ip` = :ip;", [
                                'client1_crt' => $res,
                                'ip' => $directory
                            ]);
                            
                            Storage::disk('servers')->delete($file);

                            break;
                        case 'client1.key':
                            $response['ips'][$directory] = $directory;
                    
                            $response['client1.key'] = (isset($response['client1.key']) ? $response['client1.key'] : 0) + Server::where('ip', '=', $directory)->count();
                            
                            DB::statement("UPDATE `servers` SET `client1_key` = :client1_key WHERE `servers`.`ip` = :ip;", [
                                'client1_key' => $res,
                                'ip' => $directory
                            ]);
                            
                            Storage::disk('servers')->delete($file);

                            break;
                        case 'server-conf.txt':
                            $response['ips'][$directory] = $directory;
                    
                            $response['server-conf.txt'] = (isset($response['server-conf.txt']) ? $response['ca.crt'] : 0) + Server::where('ip', '=', $directory)->count();
                            
                            DB::statement("UPDATE `servers` SET `config` = :config WHERE `servers`.`ip` = :ip;", [
                                'config' => $res,
                                'ip' => $directory
                            ]);
                            
                            Storage::disk('servers')->delete($file);

                            break;
                    }
                }
                
                foreach (Storage::disk('servers')->files($directory) as $file) {
                    $pinf = pathinfo($file);
                    $res = Storage::disk('servers')->get($file);

                    if(isset($pinf['extension'])){
                        switch ($pinf['extension']) {
                            case 'ovpn': 
                                $response['ips'][$directory] = $directory;
                    
                                $response['ovpn'] = (isset($response['ovpn']) ? $response['ovpn'] : 0) + Server::where('ip', '=', $directory)->count();
                            
                                DB::statement("UPDATE `servers` SET `config_ovpn` = :config_ovpn WHERE `servers`.`ip` = :ip;", [
                                    'config_ovpn' => $res,
                                    'ip' => $directory
                                ]);
                            
                                Storage::disk('servers')->delete($file);

                                break;
                        }
                    } 
                }
            }
        }

        return Response::json(['status' => 1, 'response' => $response]);
    }

    public function badservers()
    {
        $dir = __dir__ . '/../../../storage/badservers';
        
        if (is_dir($dir)) {
            $cdir = scandir($dir);
            
            foreach ($cdir as $key => $value) {
                if (!in_array($value,array(".",".."))) {
                    $pathinfo = pathinfo($dir . '/' . $value);
                    
                    if (isset($pathinfo['filename']) && isset($pathinfo['extension'])) {
                        if ($pathinfo['extension'] == 'txt') {
                            DB::statement("UPDATE `servers` SET `not_rating` = 0 WHERE `not_rating` = 1 and `count_connections` = 404;");
                            break;
                        }
                    }
                }
            }
            
            foreach ($cdir as $key => $value) {
                if (!in_array($value,array(".",".."))) {
                    $pathinfo = pathinfo($dir . '/' . $value);
                    
                    if (isset($pathinfo['filename']) && isset($pathinfo['extension'])) {
                        if ($pathinfo['extension'] == 'txt') {
                            DB::statement("UPDATE `servers` SET `not_rating` = 1, `count_connections` = 404 WHERE `not_rating` != 1 and `servers`.`ip` = '" . $pathinfo['filename'] . "';");
                            unlink($dir . '/' . $value);
                        }
                    }
                }
            }
        }
    }

    public function apiServersSs(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);

        $servers = [];

        $offset = 0;
        $limit = 200;

        if ($request->offset) {
            $offset = $request->offset;
        }
        if ($request->limit) {
            $limit = $request->limit;
        }

        $query = $this->server
            ->select(
                'servers.*', 
                'api_servers.status_api',
                'api_servers.status_ss',
                'api_servers.status_vpn',
                'api_servers.status_pro',
                'api_servers.status_local',
                'api_servers.status_fake',
                'api_servers.status_not_rating'
            )
            ->join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('status', '=', 1)
            ->where('api_servers.status_ss', '=', 1);

        if ($request->id) {
            $query->where('id', '=', $request->id);
        }
        if ($request->get('server')) {
            $query->where('server', '=', $request->get('server'));
        }
        if ($request->name) {
            $query->where('name', '=', $request->name);
        }
        if ($request->ip) {
            $query->where('ip', '=', $request->ip);
        }
        if ($request->type) {
            $query->where('type', '=', $request->type);
        }
        if ($request->country) {
            $query->where('country', '=', $request->country);
        }
        if ($request->date) {
            $query->where('date', '=', $request->date);
        }
        
        if ($this->auth_app_id_nm == 3) {
            $query->where('api_servers.status_fake', '=', 1);
        } else {
            $query->where('api_servers.status_fake', '=', 0);
        }                

        $count = $query->count();

        $servers = [];

        foreach ($query->offset($offset)->limit($limit)->orderBy('api_servers.sort', 'asc')->get() as
            $server) {
            $obj = new \StdClass;

            $obj->flag_url = asset('/images/' . $server->img_flag);
            $obj->map_url = asset('/images/' . $server->img_map);
            $obj->ip = $server->ip;
            $obj->country = $server->country;
            $obj->name = $server->name;
            $obj->status = strtoupper($server->status_pro == '1' ? 'PRO' : 'FREE');
            $obj->local = $server->status_local;

            $servers[] = $obj;
        }

        return Response::json($servers, 200, [], JSON_HEX_TAG);
    }

    public function apiServers(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);

        $servers = [];

        $offset = 0;
        $limit = 200;

        if ($request->offset) {
            $offset = $request->offset;
        }
        if ($request->limit) {
            $limit = $request->limit;
        }

        $query = $this->server
            ->select(
                'servers.*', 
                'api_servers.status_api',
                'api_servers.status_ss',
                'api_servers.status_vpn',
                'api_servers.status_pro',
                'api_servers.status_local',
                'api_servers.status_fake',
                'api_servers.status_not_rating'
            )
            ->join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('status', '=', 1)
            ->where('api_servers.status_vpn', '=', 1);

        if (\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id)) {
            if (!$this->prokey) {
                //$query->where('api_servers.status_pro', '=', 0);
            }
        }
        
        if ($this->auth_app_id_nm == 3) {
            $query->where('api_servers.status_fake', '=', 1);
        } else {
            $query->where('api_servers.status_fake', '=', 0);
        }
        
        if ($request->id) {
            $query->where('id', '=', $request->id);
        }
        if ($request->get('server')) {
            $query->where('server', '=', $request->get('server'));
        }
        if ($request->name) {
            $query->where('name', '=', $request->name);
        }
        if ($request->ip) {
            $query->where('ip', '=', $request->ip);
        }
        if ($request->type) {
            $query->where('type', '=', $request->type);
        }
        if ($request->country) {
            $query->where('country', '=', $request->country);
        }
        if ($request->date) {
            $query->where('date', '=', $request->date);
        }

        $count = $query->count();

        $servers = [];

        foreach ($query->offset($offset)->limit($limit)->orderBy('api_servers.sort', 'asc')->get() as
            $server) {
            $obj = new \StdClass;

            $obj->flag_url = asset('/images/' . $server->img_flag);
            $obj->map_url = asset('/images/' . $server->img_map);
            $obj->ip = $server->ip;
            $obj->country = $server->country;
            $obj->name = $server->name;
            $obj->status = strtoupper($server->status_pro == '1' ? 'PRO' : 'FREE');
            $obj->local = $server->status_local;

            $servers[] = $obj;
        }

        return Response::json($servers, 200, [], JSON_HEX_TAG);
    }

    public function apiServerSs(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);

        $servers = [];

        $offset = 0;
        $limit = 200;

        if ($request->offset) {
            $offset = $request->offset;
        }
        if ($request->limit) {
            $limit = $request->limit;
        }

        if (!$request->ip) {
            return Response::json([]);
        }

        $query = $this->server->select(['*', ])
            ->join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('status', '=', 1)
            ->where('api_servers.status_ss', '=', 1);

        if (\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id)) {
            if (!$this->prokey) {
                $query->where('api_servers.status_pro', '=', 0);
            }
        }
        
        if ($this->auth_app_id_nm == 3) {
            $query->where('api_servers.status_fake', '=', 1);
        } else {
            $query->where('api_servers.status_fake', '=', 0);
        }
        
        if ($request->id) {
            $query->where('id', '=', $request->id);
        }
        if ($request->get('server')) {
            $query->where('server', '=', $request->get('server'));
        }
        if ($request->name) {
            $query->where('name', '=', $request->name);
        }
        if ($request->ip) {
            if ($this->auth_app_id_nm == 2 || $this->auth_app_id_nm == 3 || $this->auth_app_id_nm == 4) {
                if (\App\Helpers\Setting::value('app_api_auth:' . $this->api->id)) {
                    $query->where(function ($query) use ($request) {
                        $query->orWhere('api_servers.encrypt_ip_3', '=', $request->ip);
                    });
                } else {
                    $query->where(function ($query) use ($request) {
                        $query->where('servers.ip', '=', $request->ip)
                            ->orWhere('api_servers.encrypt_ip_3', '=', $request->ip);
                    });
                }
            } else {
                if (\App\Helpers\Setting::value('app_api_auth:' . $this->api->id)) {
                    $query->where(function ($query) use ($request) {
                        $query->where('api_servers.encrypt_ip_2', '=', $request->ip);
                    });
                } else {
                    $query->where(function ($query) use ($request) {
                        $query->where('servers.ip', '=', $request->ip)
                            ->orWhere('api_servers.encrypt_ip_2', '=', $request->ip);
                    });
                }
            }
        }
        if ($request->type) {
            $query->where('type', '=', $request->type);
        }
        if ($request->country) {
            $query->where('country', '=', $request->country);
        }
        if ($request->date) {
            $query->where('date', '=', $request->date);
        }

        $server = $query->first();


        if (\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id)) {
            if ($server && $server->status_pro == "1") {
                if (!$this->prokey) {
                    $obj->error = 'pro-id not found';
                    $obj->status = 0;
                    
                    return Response::json($obj, 403, [], JSON_HEX_TAG);
                    
                    $query->where('api_servers.status_pro', '=', 0);
                }
            }
        }

        $obj = new \StdClass;

        if ($server) {
            $obj->ip = $server->ip;
            if (!($this->auth_app_id_nm == "1" && \App\Helpers\Setting::value('crypto_config_no_1-api:' . $this->api->id)) && \App\Helpers\Setting::value('conf_e-api:' . $this->api->id)) {
                $obj->config = \App\Helpers\Helper::synchron_encode(
                    $server->ss_config,
                    ($this->auth_app_id_nm == 2 || $this->auth_app_id_nm == 3 || $this->auth_app_id_nm == 4 ?  
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).'_2:'.$this->api->id) :
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).':'.$this->api->id))
                );
            } else {
                $obj->config = $server->ss_config;
            }
        } else {
            $obj->error = 'Server Not Found';
            $obj->status = 0;
        }

        return Response::json($obj, 200, [], JSON_HEX_TAG);
    }

    public function apiServer(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);

        $servers = [];

        $offset = 0;
        $limit = 200;

        $obj = new \StdClass;

        if ($request->offset) {
            $offset = $request->offset;
        }
        if ($request->limit) {
            $limit = $request->limit;
        }

        if (!$request->ip) {
            return Response::json([]);
        }

        $query = $this->server->select(['*', ])
            ->join('api_servers', 'api_servers.server_id', '=', 'servers.id')
            ->where('api_servers.api_id', '=', $this->api->id)
            ->where('api_servers.status_api', '=', 1)
            ->where('status', '=', 1)
            ->where('api_servers.status_vpn', '=', 1);
        
        if ($this->auth_app_id_nm == 3) {
            $query->where('api_servers.status_fake', '=', 1);
        } else {
            $query->where('api_servers.status_fake', '=', 0);
        }
        
        if ($request->id) {
            $query->where('id', '=', $request->id);
        }
        if ($request->get('server')) {
            $query->where('server', '=', $request->get('server'));
        }
        if ($request->name) {
            $query->where('name', '=', $request->name);
        }
        if ($request->ip) {
            if ($this->auth_app_id_nm == 2 || $this->auth_app_id_nm == 3 || $this->auth_app_id_nm == 4) {
                if (\App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id)) {
                    $query->where(function ($query) use ($request) {
                        $query->orWhere('api_servers.encrypt_ip_3', '=', $request->ip);
                    });
                } else {
                    $query->where(function ($query) use ($request) {
                        $query->where('servers.ip', '=', $request->ip)
                            ->orWhere('api_servers.encrypt_ip_3', '=', $request->ip);
                    });
                }
            } else {
                if (\App\Helpers\Setting::value('app_api_auth_encryption_app_id:' . $this->api->id)) {
                    $query->where(function ($query) use ($request) {
                        $query->where('api_servers.encrypt_ip_2', '=', $request->ip);
                    });
                } else {
                    $query->where(function ($query) use ($request) {
                        $query->where('servers.ip', '=', $request->ip)
                            ->orWhere('api_servers.encrypt_ip_2', '=', $request->ip);
                    });
                }
            }
        }
        if ($request->type) {
            $query->where('type', '=', $request->type);
        }
        if ($request->country) {
            $query->where('country', '=', $request->country);
        }
        if ($request->date) {
            $query->where('date', '=', $request->date);
        }

        $server = $query->first();


        if (\App\Helpers\Setting::value('pro_for_pro-api:' . $this->api->id)) {
            if ($server && $server->status_pro == "1") {
                if (!$this->prokey) {
                    $obj->error = 'pro-id not found';
                    $obj->status = 0;
                    
                    return Response::json($obj, 403, [], JSON_HEX_TAG);
                    
                    $query->where('api_servers.status_pro', '=', 0);
                }
            }
        }

        if ($server) {
            if ($server->config_ovpn) {
                $obj->ip = $server->ip;
                if (!($this->auth_app_id_nm == "1" && \App\Helpers\Setting::value('crypto_config_no_1-api:' . $this->api->id)) && \App\Helpers\Setting::value('conf_e-api:' . $this->api->id)) {
                    $obj->config = \App\Helpers\Helper::synchron_encode(
                        $server->config_ovpn,
                        ($this->auth_app_id_nm == 2 || $this->auth_app_id_nm == 3 || $this->auth_app_id_nm == 4 ?  
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).'_2:'.$this->api->id) :
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).':'.$this->api->id))
                    );
                } else {
                    $obj->config = $server->config_ovpn;
                }
            } else {
                $obj->ip = $server->ip;
                if (!($this->auth_app_id_nm == "1" && \App\Helpers\Setting::value('crypto_config_no_1-api:' . $this->api->id)) && \App\Helpers\Setting::value('conf_e-api:' . $this->api->id)) {
                    $obj->config = \App\Helpers\Helper::synchron_encode(
                        $server->config . "\n" . "\n<ca>\n" . $server->ca_crt . "\n</ca>\n\n" .
                            "\n<cert>\n" . $server->client1_crt . "\n</cert>\n\n" . "\n<key>\n" . $server->
                            client1_key . "\n</key>",
                        ($this->auth_app_id_nm == 2 || $this->auth_app_id_nm == 3 || $this->auth_app_id_nm == 4 ?  
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).'_2:'.$this->api->id) :
                        \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$this->api->id).':'.$this->api->id))
                    );
                } else {
                    $obj->config = $server->config . "\n" . "\n<ca>\n" . $server->ca_crt . "\n</ca>\n\n" .
                        "\n<cert>\n" . $server->client1_crt . "\n</cert>\n\n" . "\n<key>\n" . $server->
                        client1_key . "\n</key>";
                } 
            }
        } else {
            $obj->error = 'Server Not Found';
            $obj->status = 0;
        }


        return Response::json($obj, 200, [], JSON_HEX_TAG);
    }

    public function saveSelectChecked(Request $request)
    {
        if ($request->server_id) {
            Session::put('select_server_id', $request->server_id);
        } else {
            Session::forget('select_server_id');
        }

        return Response::json(['status' => 1]); 
    }
    
    public function resortSort()
    {
        $query = "UPDATE `servers` AS serv1 JOIN( SELECT `id`, ROW_NUMBER() OVER( ORDER BY sort ) AS row_num FROM servers) AS serv2 ON serv1.id = serv2.id SET serv1.sort = serv2.row_num;";
        $bindings = [];
        
        DB::statement($query, $bindings);
    }

    public function displaceSort(Request $request)
    {
        $thisServer = Server::where('id', '=', $request->server_id)->first();

        if ($thisServer) {
            if ($request->server_to_id > 0) {
                $selectServer = Server::where('id', '=', $request->server_to_id)->first();
                
                if ($selectServer) {
                    $nextServer = Server::where('sort', '>', $selectServer->sort)->where('id', '!=', $selectServer->id)->orderBy('sort', 'asc')->first();
                    
                    if ($nextServer) {
                        if ($selectServer->sort == $nextServer->sort) {
                            for ($i = 0; $i < 10000; $i++) {
                                $nextServer->sort = round((floatval($nextServer->sort)+floatval($selectServer->sort))/2, 4);
                                
                                if ($selectServer->sort != $nextServer->sort) {
                                    break;
                                }                                       
                            }
                        }
                    
                        $thisServer->sort = round((floatval($selectServer->sort)+floatval($nextServer->sort))/2, 4);
                    } else {
                        $thisServer->sort = round(floatval($selectServer->sort) + 0.1, 4);
                    }
                    
                    $thisServer->save();
                }
            } else {
                $prevServer = Server::where('sort', '<', $thisServer->sort)->where('id', '!=', $thisServer->id)->orderBy('sort', 'desc')->first();
                $nextServer = Server::where('sort', '>', $thisServer->sort)->where('id', '!=', $thisServer->id)->orderBy('sort', 'asc')->first();
           
                //exit(($prevServer?$prevServer->sort:0).'-'.($thisServer?$thisServer->sort:0).'-'.($nextServer?$nextServer->sort:0));
            
                switch ($request->type) {
                    case 'up':
                        if ($prevServer) {
                            if ($thisServer->sort == $prevServer->sort) {
                                for ($i = 0; $i < 10000; $i++) {
                                    $prevServer->sort = round((floatval($thisServer->sort)+floatval($prevServer->sort))/2, 4);
                                
                                    if ($thisServer->sort != $prevServer->sort) {
                                        break;
                                    }                                       
                                }
                            }
                            
                            $tmpsort = $prevServer->sort;
                            $prevServer->sort = round($thisServer->sort, 4);
                            $thisServer->sort = round($tmpsort, 4);
                        
                            $prevServer->save();
                            $thisServer->save();
                        }
                    
                        break;
                    case 'down':
                        if ($nextServer) {
                            if ($thisServer->sort == $nextServer->sort) {
                                for ($i = 0; $i < 10000; $i++) {
                                    $nextServer->sort = round((floatval($thisServer->sort)+floatval($nextServer->sort))/2, 4);
                                
                                    if ($thisServer->sort != $nextServer->sort) {
                                        break;
                                    }                                       
                                }
                            }
                            
                            $tmpsort = $nextServer->sort;
                            $nextServer->sort = round($thisServer->sort, 4);
                            $thisServer->sort = round($tmpsort, 4);
                        
                            $nextServer->save();
                            $thisServer->save();
                        }
                    
                        break;
                }
            }
        }
        
        $this->resortSort();

        return Response::json(['status' => 1]);
    }

    public function displaceSortApi(Request $request)
    {
        $thisServer = ApiServer::where('api_id', '=', $this->api->id)->where('id', '=', $request->api_server_id)->firstOrFail();

        if ($thisServer) {
            if ($request->api_server_to_id > 0) {
                $selectServer = ApiServer::where('api_id', '=', $this->api->id)->where('id', '=', $request->api_server_to_id)->first();
                
                if ($selectServer) {
                    $nextServer = ApiServer::where('api_id', '=', $this->api->id)->where('sort', '>', $selectServer->sort)->where('id', '!=', $selectServer->id)->orderBy('sort', 'asc')->first();
                    
                    if ($nextServer) {
                        if ($selectServer->sort == $nextServer->sort) {
                            for ($i = 0; $i < 10000; $i++) {
                                $nextServer->sort = round((floatval($selectServer->sort)+floatval($nextServer->sort))/2, 4);
                                
                                if ($selectServer->sort != $nextServer->sort) {
                                    break;
                                }                                       
                            }
                        }
                    
                        $thisServer->sort = round((floatval($selectServer->sort)+floatval($nextServer->sort))/2, 4);
                    } else {
                        $thisServer->sort = round(floatval($selectServer->sort) + 0.1, 4);
                    }
                    
                    $thisServer->save();
                }
            } else {
                $prevServer = ApiServer::where('api_id', '=', $this->api->id)->where('sort', '<', $thisServer->sort)->where('id', '!=', $thisServer->id)->orderBy('sort', 'desc')->first();
                $nextServer = ApiServer::where('api_id', '=', $this->api->id)->where('sort', '>', $thisServer->sort)->where('id', '!=', $thisServer->id)->orderBy('sort', 'asc')->first();
           
                //exit(($prevServer?$prevServer->sort:0).'-'.($thisServer?$thisServer->sort:0).'-'.($nextServer?$nextServer->sort:0));
            
                switch ($request->type) {
                    case 'up':
                        if ($prevServer) {
                            if ($thisServer->sort == $prevServer->sort) {
                                for ($i = 0; $i < 10000; $i++) {
                                    $prevServer->sort = round((floatval($thisServer->sort)+floatval($prevServer->sort))/2, 4);
                                
                                    if ($thisServer->sort != $prevServer->sort) {
                                        break;
                                    }                                       
                                }
                            }
                            
                            $tmpsort = $prevServer->sort;
                            $prevServer->sort = round($thisServer->sort, 4);
                            $thisServer->sort = round($tmpsort, 4);
                        
                            $prevServer->save();
                            $thisServer->save();
                        }   
                    
                        break;
                    case 'down':
                        if ($nextServer) {
                            if ($thisServer->sort == $nextServer->sort) {
                                for ($i = 0; $i < 10000; $i++) {
                                    $nextServer->sort = round((floatval($thisServer->sort)+floatval($nextServer->sort))/2, 4);
                                
                                    if ($thisServer->sort != $nextServer->sort) {
                                        break;
                                    }                                       
                                }
                            }
                            
                            $tmpsort = $nextServer->sort;
                            $nextServer->sort = round($thisServer->sort, 4);
                            $thisServer->sort = round($tmpsort, 4);
                        
                            $nextServer->save();
                            $thisServer->save();
                        }
                    
                        break;
                }
            }
        }
        
        $this->resortSort();

        return Response::json(['status' => 1]);
    }

    public function deletecert(Request $request)
    {
        $server = Server::where('id', '=', $request->server_id)->firstOrFail();
        
        $server->ca_crt = null;
        
        $server->save();
        
        return Response::json(['status' => 1]);
        
        return Redirect::route('apis.servers.index', ['api_id' => $this->api->id]);
    }

    public function saveOpenVpn(Request $request)
    {
        $query = $this->server->where('status', '=', '1')->where(function ($query) {
            $query->where('ca_crt', '!=', '')->whereNotNull('ca_crt');
        })->distinct('servers.ip')->limit(3000);

        foreach ($query->get() as $server) {
            Storage::disk('servers')->put(
                $server->ip.'.ovpn', $server->config . "\n" . "\n<ca>\n" . $server->ca_crt . "\n</ca>\n\n" .
                    "\n<cert>\n" . $server->client1_crt . "\n</cert>\n\n" . "\n<key>\n" . $server->
                    client1_key . "\n</key>"
            );
        }
    }

    public function saveOpenVpnAll(Request $request)
    {
        $query = $this->server->where('status', '=', '1')->where(function ($query) {
            $query->where('ca_crt', '!=', '')->whereNotNull('ca_crt');
        })->distinct('servers.ip')->limit(3000);

        foreach ($query->get() as $server) {
            Storage::disk('allservers')->put(
                $server->key . '_' . $server->ip.'.ovpn', $server->config . "\n" . "\n<ca>\n" . $server->ca_crt . "\n</ca>\n\n" .
                    "\n<cert>\n" . $server->client1_crt . "\n</cert>\n\n" . "\n<key>\n" . $server->
                    client1_key . "\n</key>"
            );
        }
    }

    public function noCheck()
    {
        $ipAddresses = Server::where('status',false)->pluck('ip')->toArray();
        $ipList = implode("\n", $ipAddresses);
        return response($ipList, 200)
            ->header('Content-Type', 'text/plain');
    }
}
