<?php

use \App\Helpers\Helper;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once(__dir__ . "/../app/Helpers/Helper.php");

$token = "eyJhbGciOiJBMjU2S1ciLCJlbmMiOiJBMjU2R0NNIn0.U8LDlhzV16zvXgwlpbXk5ZgPN1gBW-dMrWZz0ZZeHruYzKluC_9SHQ.JhZ7NhV4tJ6nX957.GWxIWAF4iKSC3fPC6pY4rQCv4ObZ6yRh5jBqT_Ila3da_uzvzcNY55gA97vo1ILXc9fwFqT-1ZI8CVnwkk32GDOhTvnwEyOCx5FbD13zreJ9jtDVjenCIj_gZYEoEJS86cTHs_MoqxNiz5q_0sbh1Vuq3fVFION5dMjl80S6nEMd_Lt2h7WwTKcQ9fKTskc57DX3VNIydilX1awYE-QCOauT8KihlMlPk1830KUvgzmL9WT1QjwzVdutZ5b1wulyxpvrkufF4j_Y2ru7oPvMo0MRCxm6nU8RmzdW3PoWZw83rY7gbExFgrRiuzd_c29XtMl-X_EiIjTpDQ9dGFA6wEG9HPMaTzAsFNCuGsi_ThJI8dEcSdSmn3mrxx2awetFfOmNGDEaXMDqwSQzkvxZgvDO2N8DNKxBsrYW1yVCSe1e--OIQZb0GG7g5h9qOpfYTsEAVLd_ZHmwrJaRVoIFjAbaOjwWFvsvPxBVdOmCan12NIXrTAkUOYjoAytKrjTuzv7bT0XQpwGrDFsfR6e4qLfGCSTPM5scWOTInZ08TlHueex7B_ZJBNHONkX7tpflTJNeHlpccwQjgfZIOoxa8_2-xeyfToR6UFatwO35Swb_AzH-jJoL5gbOEzn64nVneKf6gKPOwgRzRU0S7xqzBgWDHqDAzN02IsV1nqGdm6efxnpC4ceThjwXcPy_QvzuYYsnPMggsVM3hPYM1ClIjhJMxuZ01xxkFnKY-tEr-OIPGjsWTP--WHAMfIYDVJq_xSRVzQ5ViKNKAcqQS3m3zCTeGF4Z6suVIKeJEUJrHGyC3YHVA4E_3yxP-ruOkI9BhESB97N7z0hyV98K6fDVUq9ICBLGkd5d636UL1otG3BqPHa9zOHEtc8h8hxfygSzlvoULeqvPntHwwbF34pEzh3MrrcFRGFojQNPe4aHJ6XSdyRFwKtAirFpk-CJ8VA_CGeAd-SO2K3nGP7PK9ab4_Gj-gYZosi9Wb1eIIrCOwn30dfLOQstbr_GZiI-cDKK_l3RdXBpS2oWPP1jA2b8LJ8iJdK-qEhV4VI9krN6E6AMkBEDGIZED1NE0qpV9Jis4Gf5Ifu9CJDRzdeJilABVop2UnG0JaAA-HnHPyIYAHZPu3XAm0_PnsrNuJGsBNsH7kYWNEwYCJCFX2-rnsHseVoxODU7fXUQaKPZZYGfho_yrrvS10g.q7nTaazoFLhjAnWIJHHf3w";

$globaloptions = [];
$globaloptions[CURLOPT_RETURNTRANSFER] = true;
$globaloptions[CURLINFO_HEADER_OUT] = true;
$globaloptions[CURLOPT_HTTPHEADER] = array(
    "app-id:" . $token,
);

$ch = curl_init();
$options = $globaloptions;
$options[CURLOPT_URL] = "https://testapi.tap2free.net/api/main-servers";
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$chinfo = curl_getinfo($ch);
curl_close($ch);

$json = json_decode($response, true);

if ($chinfo['http_code'] == 200 && $json) {
    echo "<pre>";
    //print_r($json);
    
    if ($json && isset($json[0])) {
        echo "ip: " . $json[0]["ip"] . "\n";
        echo "crypt2(2ehmcw30h0tewn1otz2): " . Helper::crypt($json[0]["ip"], 2, "2ehmcw30h0tewn1otz2") . "\n";
        echo "crypt2(retlfbe1uyt23z): " . Helper::crypt($json[0]["ip"], 2, "retlfbe1uyt23z") . "\n";
        
        $ch = curl_init();
        $options = $globaloptions;
        $options[CURLOPT_HTTPHEADER] = array(
    "app-id:" . md5($token),
);
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





























exit;

$globaloptions = [];
$globaloptions[CURLOPT_RETURNTRANSFER] = true;
$globaloptions[CURLINFO_HEADER_OUT] = true;
$globaloptions[CURLOPT_HTTPHEADER] = array(
    "app-id:" . Helper::crypt("53b9298aad65fe79d788ada3dcd66023", 2, "retlfbe1uyt23z"),
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
    //print_r($json);
    
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







