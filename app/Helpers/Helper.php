<?php

namespace App\Helpers;

use \Storage;
use \DB;

use Google\Client;
use Google\Service\PlayIntegrity;
use Google\Service\PlayIntegrity\DecodeIntegrityTokenRequest;

class Helper
{
    public static $google_token = false;
    
    public static function getGoogleTokenInfo()
    {
        self::getGoogleToken();
        
        return self::$google_token;
    }
    
    public static function deleteGoogleToken()
    {
        if (file_exists(__dir__ . '/google_token.txt')) {
            unlink(__dir__ . '/google_token.txt');
        }
    }
    
    public static function getGoogleToken()
    {
        if (self::$google_token === false) {
            self::$google_token = null;
            
            if (file_exists(__dir__ . '/google_token.txt')) {
                $data = json_decode(file_get_contents(__dir__ . '/google_token.txt'), true);
                
                if($data && isset($data['access_token'])){
                    self::$google_token = $data;
                }
            }
        }
        
        if (is_array(self::$google_token) && isset(self::$google_token["access_token"])) {
            if (self::$google_token['expires_time'] > time()) {
                
            } elseif(isset(self::$google_token['expires_time'])) {
                $params = array(
                    'client_id' => env('OAuthGoogleId'),
                    'client_secret' => env('OAuthGoogleSecretKey'),
                    'refresh_token' => self::$google_token['refresh_token'],
                    'grant_type' => 'refresh_token',
                );
                
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
                    if (!isset($tokenInfo['refresh_token']) && isset(self::$google_token['refresh_token'])) {
                        $tokenInfo['refresh_token'] = self::$google_token['refresh_token'];
                        $tokenInfo['copy_refresh_token_old'] = true;
                    }
                    
                    self::setGoogleToken($tokenInfo);
                    
                    self::$google_token = $tokenInfo;
                }
            }
        }
        
        if (is_array(self::$google_token) && isset(self::$google_token["access_token"])) {
            return self::$google_token["access_token"];
        }
        
        return null;
    }

    public static function setGoogleToken(&$token)
    {
        if ($token && isset($token['access_token']) && isset($token['expires_in'])) {
            $token['expires_time'] = time() + $token['expires_in'] - 60;
            
            file_put_contents(__dir__ . '/google_token.txt', json_encode($token));
        }
    }
    
    public static $apiid = 0;
    
    public static function log($str)
    {
        if (env('SAVE_LOG')) {
            file_put_contents(
                __dir__ . "/../../log.txt", 
                date("Y-m-d H:i") . 
                (isset($_SERVER['REQUEST_URI']) ? " " . $_SERVER['REQUEST_URI'] : "") . 
                (isset($_POST) && !empty($_POST) ? " post:" . http_build_query($_POST) : "") . 
                " Message: " . $str . "\r\n",
                FILE_APPEND
            );
        }
    }

    // $config = base_path("config/google.json")
    // $package = 'vpn.russia_tap2free'
    public static function getAppInfoByToken($config, $package, $token)
    {
        try {
            $client = new Client();
            $client->setAuthConfig($config);
            $client->addScope(PlayIntegrity::PLAYINTEGRITY);
            $service = new PlayIntegrity($client);
            $tokenRequest = new DecodeIntegrityTokenRequest();
            $tokenRequest->setIntegrityToken($token);
            $result = $service->v1->decodeIntegrityToken($package, $tokenRequest);

            //$deviceVerdict = $result->deviceIntegrity->deviceRecognitionVerdict;
            //$appVerdict = $result->appIntegrity->appRecognitionVerdict;
            //$accountVerdict = $result->accountDetails->appLicensingVerdict;

            $deviceVerdict = $result->getTokenPayloadExternal()->getDeviceIntegrity()->
                deviceRecognitionVerdict;
            $appVerdict = $result->getTokenPayloadExternal()->getAppIntegrity()->
                appRecognitionVerdict;
            $accountVerdict = $result->getTokenPayloadExternal()->getAccountDetails()->
                appLicensingVerdict;

            return [
                'status' => 1,
                'deviceVerdict' => $deviceVerdict[0],
                'appVerdict' => $appVerdict,
                'accountVerdict' => $accountVerdict,
                'time' => intval($result->getTokenPayloadExternal()->getRequestDetails()->timestampMillis / 1000)
            ]; 
        }
        catch (\Exception $e) {
            return [
                'status' => 0,
                'error' => $e->getMessage()
            ];         
        }
    }

    public static function crypt($string, $encryption_method, $encryption_key = "")
    {
        if (false && \App\Helpers\Setting::value('app_encryption_setting')) {
            $settings[1] = \App\Helpers\Setting::value('app_encryption_setting_1:' . self::
                $apiid);
            $settings[2] = \App\Helpers\Setting::value('app_encryption_setting_2:' . self::
                $apiid);
            $settings[3] = \App\Helpers\Setting::value('app_encryption_setting_3:' . self::
                $apiid);
            $settings[4] = \App\Helpers\Setting::value('app_encryption_setting_4:' . self::
                $apiid);
            $settings[5] = \App\Helpers\Setting::value('app_encryption_setting_5:' . self::
                $apiid);

            foreach ($settings as $key => $value) {
                if (!($value >= 1 && $value <= 5)) {
                    $value = 1;
                }
            }

            if (mb_strlen($string) >= 5) {
                $arr = stringChunked($string, 5);
            } else {
                $arr = [$string, $string, $string, $string, $string];
            }

            $cryptString = '';

            foreach ($arr as $key => $value) {
                $method = isset($settings[$key + 1]) ? $settings[$key + 1] : 1;

                switch ($method) {
                    case '1';
                        $cryptString .= self::cryptMethod1($value, $encryption_key);
                        break;
                    case '2';
                        $cryptString .= self::cryptMethod2($value, $encryption_key);
                        break;
                    case '3';
                        $cryptString .= self::cryptMethod3($value, $encryption_key);
                        break;
                    case '4';
                        $cryptString .= self::cryptMethod4($value, $encryption_key);
                        break;
                    case '5';
                        $cryptString .= self::cryptMethod5($value, $encryption_key);
                        break;
                    default;
                        $cryptString .= self::cryptDefault($value, $encryption_key);
                        break;
                }
            }

            return self::cryptHash($cryptString);
        } else {
            switch ($encryption_method) {
                case '1';
                    return self::cryptHash(self::cryptMethod1($string, $encryption_key));
                    break;
                case '2';
                    return self::cryptHash(self::cryptMethod2($string, $encryption_key));
                    break;
                case '3';
                    return self::cryptHash(self::cryptMethod3($string, $encryption_key));
                    break;
                case '4';
                    return self::cryptHash(self::cryptMethod4($string, $encryption_key));
                    break;
                case '5';
                    return self::cryptHash(self::cryptMethod5($string, $encryption_key));
                    break;
                default;
                    return self::cryptHash(self::cryptDefault($string, $encryption_key));
                    break;
            }
        }
    }

    public static function cryptHash($string)
    {
        return md5($string);
    }

    public static function cryptMethod1($value, $key)
    {
        $result = '';

        for ($i = 0; $i < mb_strlen($key); $i++) {
            $result = $result . $value;
        }

        for ($i = 0; $i < floor(mb_strlen($key) / 2); $i++) {
            $result = str_replace(mb_substr($key, $i, 1), mb_substr($key, $i + floor(mb_strlen
                ($key) / 2), 1), $result);
        }

        return $result;
    }

    public static function cryptMethod2($value, $key)
    {
        $result = '';

        for ($i = 0; $i < mb_strlen($value) && $i < mb_strlen($key); $i++) {
            $char1 = mb_substr($value, $i, 1);
            $char2 = mb_substr($key, $i, 1);

            if ($i % 3 == '0') {
                $result = $char1 . $result . $char2;
            } else {
                $result = $char2 . $result . $char1;
            }
        }

        return $result;
    }

    public static function cryptMethod3($value, $key)
    {
        $result = '';

        $result = $key . $value . mb_substr(strrev($value), 0, -1) . strrev(mb_substr($key,
            0, -1));

        return $result;
    }

    public static function cryptMethod4($value, $key)
    {
        $result = '';

        for ($i = 0; $i < mb_strlen($value); $i++) {
            $char = mb_substr($value, $i, 1);

            $result = $char . $result;

            if ($i % 4 == '0') {
                $result = $key . $result . $key;
            }
        }

        return $result;
    }

    public static function cryptMethod5($value, $key)
    {
        $result = '';

        $result = $value;

        for ($i = 0; $i < mb_strlen($key); $i++) {
            $char = mb_substr(strrev($key), $i, 2);

            if (mb_strpos($value, $char) === false) {
                $result = $char . $result;
            } else {
                $result = $result . $char;
            }
        }

        return $result;
    }

    public static function cryptDefault($value, $key)
    {
        $result = '';

        $result .= $value;
        $result .= $key;

        return ($result);
    }

    public static function prepareName($value)
    {
        $value = preg_replace("#[^a-zA-Z0-9АаБбВвГгҐґДдЕеЄєЭэЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЮюЯяЬьЪъёЫы_\-.\s/]#",
            ' ', $value);

        $value = preg_replace("#\s{2,}#", ' ', $value);

        return $value;
    }

    public static function translit($s, $del = '_')
    {
        $s = (string )$s; // преобразуем в строковое значение

        $s = mb_convert_encoding($s, "UTF-8");

        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
        $s = trim($s); // убираем пробелы в начале и конце строки
        $s = function_exists('mb_strtolower') ? mb_strtolower($s):
        strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr($s, array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ы' => 'y',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'ъ' => '',
            'ь' => '',
            "і" => "i",
            "ї" => "i",
            "є" => "ie"));

        $s = preg_replace("/[^0-9a-z]/i", $del, $s); // очищаем строку от недопустимых символов

        $s = preg_replace("#" . ($del == '.' ? '\.' : $del) . "{2,}#", $del, $s);

        $s = trim($s, $del);

        return $s; // возвращаем результат
    }

    public static function saveEventImage($new_filename, $path_tmp_image, $path_old_image = null,
        $flag_del_tmp_img = true, $extension = null)
    {
        $result = [];

        if (file_exists($path_tmp_image)) {
            if (!$extension) {
                $pathinfo = pathinfo($path_tmp_image);

                if (isset($pathinfo['extension'])) {
                    if ($pathinfo['extension'] == 'jpeg' || $pathinfo['extension'] == 'jpg' || $pathinfo['extension'] ==
                        'png' || $pathinfo['extension'] == 'gif') {

                        $extension = $pathinfo['extension'];
                    }
                }
            }

            if ($extension && ($extension == 'jpeg' || $extension == 'jpg' || $extension ==
                'png' || $extension == 'gif')) {

                if (!is_dir(public_path() . '/images' . date('/Y/m/d/'))) {
                    mkdir(public_path() . '/images' . date('/Y/m/d/'), 0777, true);
                }

                $path_new_file_original = '/images' . date('/Y/m/d/') . $new_filename . '.' . $extension;
                $path_new_file_medium = '/images' . date('/Y/m/d/') . $new_filename . '-medium.' .
                    $extension;
                $path_new_file_small = '/images' . date('/Y/m/d/') . $new_filename . '-small.' .
                    $extension;

                list($width_orig, $height_orig, $imageType) = getimagesize($path_tmp_image);

                $sourceImage = null;

                switch ($imageType) {
                    case 1:
                        $sourceImage = imagecreatefromgif($path_tmp_image);
                        break;
                    case 2:
                        $sourceImage = imagecreatefromjpeg($path_tmp_image);
                        break;
                    case 3:
                        $sourceImage = imagecreatefrompng($path_tmp_image);
                        break;
                }

                if ($sourceImage) {
                    switch ($imageType) {
                        case 1:
                            if (imagegif($sourceImage, public_path() . $path_new_file_original)) {
                                $result['original'] = $path_new_file_original;
                            }

                            break;
                        case 2:
                            if (imagejpeg($sourceImage, public_path() . $path_new_file_original, 100)) {
                                $result['original'] = $path_new_file_original;
                            }

                            break;
                        case 3:
                            if (imagepng($sourceImage, public_path() . $path_new_file_original, 0)) {
                                $result['original'] = $path_new_file_original;
                            }

                            break;
                    }

                    $ratio_orig = $width_orig / $height_orig;

                    if ($width_orig > 450) {
                        $new_width = 450;
                        $new_height = ceil($new_width / $ratio_orig);

                        $destinationImage = imagecreatetruecolor($new_width, $new_height);

                        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $new_width, $new_height,
                            $width_orig, $height_orig);

                        if ($destinationImage) {
                            switch ($imageType) {
                                case 1:
                                    if (imagegif($destinationImage, public_path() . $path_new_file_medium)) {
                                        $result['medium'] = $path_new_file_medium;
                                    }

                                    break;
                                case 2:
                                    if (imagejpeg($destinationImage, public_path() . $path_new_file_medium, 100)) {
                                        $result['medium'] = $path_new_file_medium;
                                    }

                                    break;
                                case 3:
                                    if (imagepng($destinationImage, public_path() . $path_new_file_medium, 0)) {
                                        $result['medium'] = $path_new_file_medium;
                                    }

                                    break;
                            }

                            imagedestroy($destinationImage);
                        }
                    } elseif (isset($result['original'])) {
                        $result['medium'] = $result['original'];
                    }

                    if ($width_orig > 100) {
                        $new_width = 100;
                        $new_height = ceil($new_width / $ratio_orig);

                        $destinationImage = imagecreatetruecolor($new_width, $new_height);

                        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $new_width, $new_height,
                            $width_orig, $height_orig);

                        if ($destinationImage) {
                            switch ($imageType) {
                                case 1:
                                    if (imagegif($destinationImage, public_path() . $path_new_file_small)) {
                                        $result['small'] = $path_new_file_small;
                                    }

                                    break;
                                case 2:
                                    if (imagejpeg($destinationImage, public_path() . $path_new_file_small, 100)) {
                                        $result['small'] = $path_new_file_small;
                                    }

                                    break;
                                case 3:
                                    if (imagepng($destinationImage, public_path() . $path_new_file_small, 0)) {
                                        $result['small'] = $path_new_file_small;
                                    }

                                    break;
                            }

                            imagedestroy($destinationImage);
                        }
                    } elseif (isset($result['original'])) {
                        $result['small'] = $result['original'];
                    }

                    imagedestroy($sourceImage);
                }
            }

            if ($flag_del_tmp_img) {
                unlink($path_tmp_image);
            }
        }

        return $result;
    }

    public static function synchron_encode($string = '', $skey = '')
    {
        $codechars = [];
        $codestrs = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=");
        foreach ($codestrs as $key => $char) {
            $codechars[0][$key] = $char;
            $codechars[1][$char] = $key;
        }
        $codechars[2] = count($codestrs) - 1;

        $_i = 0;

        $string = str_split(base64_encode($string));
        $skey = str_split(base64_encode($skey));

        foreach ($string as $key => $char) {
            $ord = $codechars[1][$char];

            $ord += $codechars[2] - $codechars[1][$skey[$_i]];
            if ($ord > $codechars[2]) {
                $ord = 0 + ($ord - $codechars[2] - 1);
            }

            $string[$key] = $codechars[0][$ord];

            $_i++;
            if (!isset($skey[$_i])) {
                $_i = 0;
            }
        }

        $string = implode("", $string);

        return $string;
    }

    public static function synchron_decode($string = '', $skey = '')
    {
        $codechars = [];
        $codestrs = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=");
        foreach ($codestrs as $key => $char) {
            $codechars[0][$key] = $char;
            $codechars[1][$char] = $key;
        }
        $codechars[2] = count($codestrs) - 1;

        $_i = 0;

        $string = str_split($string);
        $skey = str_split(base64_encode($skey));

        foreach ($string as $key => $char) {
            $ord = $codechars[1][$char];

            $ord -= ($codechars[2] - $codechars[1][$skey[$_i]]);
            if ($ord < 0) {
                $ord = $codechars[2] - (0 - $ord - 1);
            }

            $string[$key] = $codechars[0][$ord];

            $_i++;
            if (!isset($skey[$_i])) {
                $_i = 0;
            }
        }

        $string = implode("", $string);

        return base64_decode($string);
    }
}

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $split_length = 1, $encoding = null)
    {
        if (null !== $string && !\is_scalar($string) && !(\is_object($string) && \method_exists
            ($string, '__toString'))) {
            trigger_error('mb_str_split(): expects parameter 1 to be string, ' . \gettype($string) .
                ' given', E_USER_WARNING);
            return null;
        }
        if (null !== $split_length && !\is_bool($split_length) && !\is_numeric($split_length)) {
            trigger_error('mb_str_split(): expects parameter 2 to be int, ' . \gettype($split_length) .
                ' given', E_USER_WARNING);
            return null;
        }
        $split_length = (int)$split_length;
        if (1 > $split_length) {
            trigger_error('mb_str_split(): The length of each segment must be greater than zero',
                E_USER_WARNING);
            return false;
        }
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        } else {
            $encoding = (string )$encoding;
        }

        if (!in_array($encoding, mb_list_encodings(), true)) {
            static $aliases;
            if ($aliases === null) {
                $aliases = [];
                foreach (mb_list_encodings() as $encoding) {
                    $encoding_aliases = mb_encoding_aliases($encoding);
                    if ($encoding_aliases) {
                        foreach ($encoding_aliases as $alias) {
                            $aliases[] = $alias;
                        }
                    }
                }
            }
            if (!in_array($encoding, $aliases, true)) {
                trigger_error('mb_str_split(): Unknown encoding "' . $encoding . '"',
                    E_USER_WARNING);
                return null;
            }
        }

        $result = [];
        $length = mb_strlen($string, $encoding);
        for ($i = 0; $i < $length; $i += $split_length) {
            $result[] = mb_substr($string, $i, $split_length, $encoding);
        }
        return $result;
    }
}

function stringChunked($text, $parts)
{
    $partSize = ceil(mb_strlen($text) / $parts);
    $tailSize = mb_strlen($text) % $parts;

    $arr = [];
    for ($i = 0; $i < $parts; $i++) {
        array_push($arr, mb_substr($text, $i * $partSize, $partSize));
    }
    return $arr;
}
