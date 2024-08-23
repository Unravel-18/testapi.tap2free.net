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
    "app-id:" . "CjUtnEws8uLTKJv0CedI",
);

$ch = curl_init();
$options = $globaloptions;
$options[CURLOPT_URL] = "https://api.tap2free.net/api/key/generate";
$options[CURLOPT_POST] = true;
$options[CURLOPT_POSTFIELDS] = http_build_query([
    "secret_key" => "asd2345rgfsdfSDdf",
    "days" => 7,
]);
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$chinfo = curl_getinfo($ch);
curl_close($ch);

$json = json_decode($response, true);

if ($json) {
    echo "<pre>";
    
    print_r($json);
} else {
    echo $response;
}

