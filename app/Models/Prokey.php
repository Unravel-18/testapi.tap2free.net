<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Support\Str;

class Prokey extends Model
{
    protected $connection = 'mysql_prokeys';
    
    public function getProId()
    {
        if (!$this->pro_id) {
            for ($i = 0; $i < 1000 && (!$this->pro_id || Prokey::where("pro_id", "=", $this->pro_id)->count() > 0); $i++) {
                //$this->pro_id = Str::random(20);
                $this->pro_id = sha1($this->token);
            }
            
            $this->save();
        }
        
        return $this->pro_id;
    }
    
    public function isAtive()
    {
        if ($this->status == "1" && $this->expiry_time > time()) {
            return true;
        } else {
            //$this->delete();
        }
        
        return false;
    }
    
    public static function getProkeyByPurchase($packageName, $subscriptionId, $token, $prokey = null)
    {
        if (!$prokey) {
            $prokey = Prokey::where("token", $token)->first();
        }
        
        $info = Prokey::getInfoPurchase($packageName, $subscriptionId, $token);
        
        if ($info) {
            if (isset($info["orderId"]) && isset($info["paymentState"]) && isset($info["startTimeMillis"]) && isset($info["expiryTimeMillis"])) {
                if ($info["expiryTimeMillis"] > time()) {
                    if (!$prokey) {
                        $prokey = new Prokey;
                    
                        $prokey->package_name = $packageName;
                        $prokey->subscription_id = $subscriptionId;
                        $prokey->token = $token;
                        $prokey->status = 1;
                    }
                }
                
                if ($info["paymentState"] >= "0") {
                    if ($prokey) {
                        $prokey->payment_state = $info["paymentState"];
                    }
                } else {
                    if ($prokey) {
                        $prokey->payment_state = null;
                    }
                }
                
                if (true) {
                    //print_r($info);
                    //echo "\n";echo date("Y-m-d H:i:s");
                    //echo "\n";echo date("Y-m-d H:i:s", intval($info["startTimeMillis"]/1000));
                    //echo "\n";echo date("Y-m-d H:i:s", intval($info["expiryTimeMillis"]/1000));
                    //echo "\n";
                                        
                    if ($prokey) {
                        $prokey->order_id = $info["orderId"];
                        $prokey->start_time = intval($info["startTimeMillis"]/1000);
                        $prokey->expiry_time = intval($info["expiryTimeMillis"]/1000);
                    }
                }
            }
        }
        
        if ($prokey) {
            if ($prokey->expiry_time > time()) {
                $prokey->last_check_time_at = date("Y-m-d H:i:s");
                
                $prokey->save();
            } else {
                $prokey->delete();
                $prokey = null;                
            }
        }
        
        return $prokey;
    }
    
    public static function getInfoPurchase($packageName, $subscriptionId, $token)
    {
        if (Helper::getGoogleToken()) {
            $response = null;
            
            if (false) { // testing
                $response = '{
                    "startTimeMillis": "1690039133999",
                    "expiryTimeMillis": "1695395924909",
                    "autoRenewing": true,
                    "priceCurrencyCode": "UAH",
                    "priceAmountMicros": "80000000",
                    "countryCode": "UA",
                    "developerPayload": "",
                    "paymentState": 1,
                    "orderId": "GPA.3348-5687-5773-51830..0",
                    "acknowledgementState": 1,
                    "kind": "androidpublisher#subscriptionPurchase"
                }';
            }
            
            if (!$response) {
                $ch = curl_init();

                // Google Play Android Developer API
                $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/" .
                    $packageName . "/purchases/subscriptions/" . $subscriptionId .
                    "/tokens/" . $token . "?key=" . env("GOOGLE_API_KEY");
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . Helper::getGoogleToken(),
                    'Content-Type: application/json',
                ));
                
                if (!curl_errno($ch)) {
                    $response = curl_exec($ch);
                }
                
                curl_close($ch);
            }

            if ($response) {
                $json = json_decode($response, true);
                
                if (is_array($json)) {
                    if (isset($json["error"])) {
                        if (isset($json["error"]["code"])) {
                            switch ($json["error"]["code"]) {
                                case "400": // API key not valid. Please pass a valid API key.
                                    self::actionErrorAuth(400);
                                    
                                    break;
                                case "401": // Request had invalid authentication credentials. Expected OAuth 2 access token. Error auth by OAuth 2 access token.
                                    self::actionErrorAuth(401);
                                    
                                    break;
                            }
                        }
                    }
                    
                    if (!isset($json["error"]) && isset($json["orderId"])) {
                        if (file_exists(__dir__ . "/count_error_auth.txt")) {
                            unlink(__dir__ . "/count_error_auth.txt");
                        }
                        
                        return $json;
                    }
                }
            }
        }
        
        return null;
    }
    
    public static $count_error_auth = false;
    
    public static function actionErrorAuth($code = null)
    {
        if (self::$count_error_auth === false) {
            if (file_exists(__dir__ . "/count_error_auth.txt")) {
                self::$count_error_auth = json_decode(file_get_contents(__dir__ . "/count_error_auth.txt"), true);
            }
            
            if (!is_array(self::$count_error_auth)) {
                self::$count_error_auth = [
                    "count" => 0,
                    "time" => 0,
                ];
            }
        }
        
        if (self::$count_error_auth["time"] > time() - 3600) {
            return;
        }
        
        self::$count_error_auth["count"]++;
        self::$count_error_auth["time"] = time();
        
        file_put_contents(__dir__ . "/count_error_auth.txt", json_encode(self::$count_error_auth));
        
        if (self::$count_error_auth["count"] < 48) {
            $post_fields = array(
                'chat_id' => env('TELGRAM_CHAT'),
                'parse_mode' => 'html',
                'text' => "Ошибка авторизации GOOGLE API !!! \n",
            );
            
            switch ($code) {
                case "400":
                    $post_fields["text"] .= "API key not valid.";
                    break;
                case "401":
                    $post_fields["text"] .= "Error auth by OAuth 2 access token.";
                    break;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . env('TELGRAM_BOT') . "/sendMessage");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

            curl_exec($ch);
    
            curl_close($ch);
        }
    }
}
