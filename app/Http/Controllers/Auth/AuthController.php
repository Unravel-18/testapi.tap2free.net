<?php

namespace App\Http\Controllers\Auth;

use DB;
use Auth;
use Socialite;
use Redirect;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Helper;
use Session;
use View;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        Session::forget('auth');
        
        return Redirect::route('servers.index');
    } 
    
    public function login(Request $request)
    {
        return View::make('auth.login');
    }
    
    public function auth(Request $request)
    {
        if ($request->login == env('ADMIN_LOGIN') && $request->password == env('ADMIN_PASSWORD')) {
            Session::put('auth', '1');
            
            return Redirect::route('servers.index');
        }
        
        return View::make('auth.login');
    }
    
    public function auth2GoogleTokenDelete(Request $request)
    {
        Helper::deleteGoogleToken();
        
        return Redirect::route('prokeys.index', []);
    }
    
    public function auth2Google(Request $request)
    {
        return Redirect::route('prokeys.index', []);
        
        echo '<meta charset="utf-8" />';
        
        $scopes = [
            //'https://www.googleapis.com/auth/userinfo.email',
            //'https://www.googleapis.com/auth/userinfo.profile',
            
            //'https://www.googleapis.com/auth/webmasters',
            //'https://www.googleapis.com/auth/webmasters.readonly',
            
            'https://www.googleapis.com/auth/androidpublisher',
        ];
        
        //echo route('auth2.google.token');exit;
        
        ?>
        <a rel="nofollow" style="font-size: inherit!important;" class="btn" title="Google" href="<?= env('OAuthGoogleUrl'). '?' . urldecode(http_build_query([
        'redirect_uri'  => route('auth2.google.token'),
        'response_type' => 'code',
        'access_type' => 'offline',
        'client_id'     => env('OAuthGoogleId'),
        'scope'         => implode(' ', $scopes)])) ?>">
          Получить токен
        </a>
        <?php
        
        echo "<br />";
        
        echo Helper::getGoogleToken();
    }

    public function auth2GoogleToken(Request $request)
    {
        if ($request->code) {
            $result = false;

            $params = array(
                'client_id' => env('OAuthGoogleId'),
                'client_secret' => env('OAuthGoogleSecretKey'),
                'redirect_uri' => route('auth2.google.token'),
                'grant_type' => 'authorization_code',
                'code' => $request->code);

            $url = 'https://accounts.google.com/o/oauth2/token';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);

            $tokenInfo = json_decode($result, true);
            
            if (isset($tokenInfo['access_token'])) {
                Helper::setGoogleToken($tokenInfo);
                
                //echo "SUCCESS. TOKEN: ";
                
                //echo "<br />";
        
                //echo Helper::getGoogleToken();
            } else {
                //echo 'fail tokenInfo';
            }
        } else {
            //echo 'not request code';
        }
        
        return Redirect::route('prokeys.index', []);
    }
}
