<?php

use \App\Helpers\Helper;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once(__dir__ . "/../app/Helpers/Helper.php");

$globaloptions = [];
$globaloptions[CURLOPT_RETURNTRANSFER] = true;
$globaloptions[CURLINFO_HEADER_OUT] = true;
$globaloptions[CURLOPT_HTTPHEADER] = array(
    "app-id:" . "JhG4dT8JiZjM39JoLiCF",
);

$ch = curl_init();
$options = $globaloptions;
$options[CURLOPT_URL] = "https://testapi.tap2free.net/api/main-servers";
//$options[CURLOPT_POST] = true;
//$options[CURLOPT_POSTFIELDS] = http_build_query([]);
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$chinfo = curl_getinfo($ch);
curl_close($ch);

$json = json_decode($response, true);

if ($chinfo['http_code'] == 200 && $json) {
    echo "<pre>";
    print_r($json);
    
    if ($json && isset($json[0])) {
        echo "ip: " . $json[0]["ip"] . "\n";
        echo "crypt2(2ehmcw30h0tewn1otz2): " . Helper::crypt($json[0]["ip"], 2, "2ehmcw30h0tewn1otz2") . "\n";
        echo "crypt2(retlfbe1uyt23z): " . Helper::crypt($json[0]["ip"], 2, "retlfbe1uyt23z") . "\n";
        
        $ch = curl_init();
        $options = $globaloptions;
        $options[CURLOPT_URL] = "https://testapi.tap2free.net/api/main-server?ip=" . Helper::crypt($json[0]["ip"], 2, "retlfbe1uyt23z");
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $chinfo = curl_getinfo($ch);
        curl_close($ch);
        
        $json = json_decode($response, true);
        
        print_r($options);
        
        if ($json) {
            if (!stripos($json['config'], "\n")) {
                $json['config'] = Helper::synchron_decode($json['config'], "retlfbe1uyt23z");
            }
            print_r($json);
        } else {
            print_r($response);
        }
    }  
} else {
    print_r($response);
}







