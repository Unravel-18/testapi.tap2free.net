<?php

namespace App\Http\Middleware;

use Redirect;
use Auth;
use Closure;
use Session;

class Filter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $params = null)
    {
        if ($params) {
            $params = explode('|', $params);

            foreach ($params as $param) {
                if ($param == 'auth_admin') {
                    if(Session::get('auth') != '1') { 
                        return Redirect::route('auth.login');
                    }
                } elseif ($param == 'auth_user_agent') {
                    if(\App\Helpers\Setting::value('app_api_auth')){
                        if(array_get(getallheaders(), 'app_id') != \App\Helpers\Setting::value('app_id')){
                            abort(404);
                        }
                    }
                } elseif ($param == 'ip') {
                    if (env('ACCESS_IP')) {
                        if(!in_array($request->ip(), config('admin_ips'))){
                            abort(404);
                        }
                    }
                } else {
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
