<?php

namespace App\Providers;

use App\Extensions\SessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use App\Models\Server;
use App\Models\Faq;
use App\Models\Language;
use App\Models\DataFaq;
use App\Models\ApiServer;
use App\Models\ApiFaq;
use App\Models\Api;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Session::extend('session', function ($app) {
            return new SessionHandler;
        });
        
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $this->app['request']->server->set('HTTPS', true);
        }
        
        Server::saving(function ($server) {
            $tmp = \App\Helpers\Helper::$apiid;
            
            foreach ($server->ApiServers as $ApiServer) {
                \App\Helpers\Helper::$apiid = $ApiServer->api_id;
                
                if ($server->ss_config) {
                    //$ApiServer->status_ss = 1;
                } else {
                    $ApiServer->status_ss = 0;
                }
                if ($server->ca_crt) {
                    //$ApiServer->status_vpn = 1;
                } else {
                    $ApiServer->status_vpn = 0;
                }
                $ApiServer->encrypt_ip = \App\Helpers\Helper::crypt(
                    $server->ip,
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key:'.$ApiServer->api_id)
                );
                $ApiServer->encrypt_ip_2 = \App\Helpers\Helper::crypt(
                    $server->ip,
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id).':'.$ApiServer->api_id)
                );
                $ApiServer->encrypt_ip_3 = \App\Helpers\Helper::crypt(
                    $server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id).'_2:'.$ApiServer->api_id)
                );
                
                $ApiServer->save();
            }
            
            \App\Helpers\Helper::$apiid = $tmp;
        });
        
        ApiServer::saving(function ($ApiServer) {
            if ($ApiServer->status_ss || $ApiServer->status_vpn) {
                $ApiServer->status_api = 1;
            } else {
                $ApiServer->status_api = 0;
            }
        });
        
        Api::deleting(function ($api) {
            ApiServer::where('api_id', '=', $api->id)->chunk(100, function ($ApiServers) {
                foreach ($ApiServers as $ApiServer) {
                    $ApiServer->delete();
                }
            });
            ApiFaq::where('api_id', '=', $api->id)->chunk(100, function ($ApiFaqs) {
                foreach ($ApiFaqs as $ApiFaq) {
                    $ApiFaq->delete();
                }
            });
        });
        
        Server::deleting(function ($server) {
            ApiServer::where('server_id', '=', $server->id)->chunk(100, function ($ApiServers) {
                foreach ($ApiServers as $ApiServer) {
                    $ApiServer->delete();
                }
            });
        });
        
        Faq::deleting(function ($faq) {
            ApiFaq::where('faq_id', '=', $faq->id)->chunk(100, function ($ApiFaqs) {
                foreach ($ApiFaqs as $ApiFaq) {
                    $ApiFaq->delete();
                }
            });
        });
        
        Server::creating(function ($server) {
            $server->sort = Server::max('sort') + 1;
        });
        
        ApiServer::creating(function ($ApiServer) {
            $ApiServer->sort = ApiServer::max('sort') + 1;
        });
        
        ApiServer::saving(function ($ApiServer) {
            $tmp = \App\Helpers\Helper::$apiid;
            
            if ($ApiServer->server) {
                \App\Helpers\Helper::$apiid = $ApiServer->api_id;
                $ApiServer->encrypt_ip = \App\Helpers\Helper::crypt(
                    $ApiServer->server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key:'.$ApiServer->api_id)
                );
                $ApiServer->encrypt_ip_2 = \App\Helpers\Helper::crypt(
                    $ApiServer->server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id).':'.$ApiServer->api_id)
                );
                $ApiServer->encrypt_ip_3 = \App\Helpers\Helper::crypt(
                    $ApiServer->server->ip, 
                    \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id),
                    \App\Helpers\Setting::value('app_api_auth_encryption_key_'.\App\Helpers\Setting::value('app_api_auth_encryption_method:'.$ApiServer->api_id).'_2:'.$ApiServer->api_id)
                );
            }
            
            \App\Helpers\Helper::$apiid = $tmp;
        });
        
        Faq::creating(function ($obj) {
            $obj->sort = Faq::max('sort') + 1;
        });
        
        Faq::deleting(function ($obj) {
            DataFaq::where('faq_id', '=', $obj->id)->delete();
        });
        
        Language::creating(function ($obj) {
            $obj->sort = Language::max('sort') + 1;
        });
        
        DataFaq::creating(function ($obj) {
            $obj->sort = DataFaq::max('sort') + 1;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
