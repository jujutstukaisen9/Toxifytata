<?php

error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($id)) {
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid 'id' parameter"]);
    exit;
}

function fetchContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return null;
    }
    curl_close($ch);
    return $response;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$id = basename($id);
$cache_dir = "_cache_/";
$cacheTime = 60;
$cacheFile = $cache_dir . "TP-$id.json";

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    header('Content-Type: application/json');
    readfile($cacheFile);
    exit;
}

$data = json_decode(fetchContent("https://fox.toxic-gang.xyz/tata/key/$id"), true);
$kid = $data[0]['data']['licence1'];
$key = $data[0]['data']['licence2'];

$binary_kid = hex2bin($kid);
$binary_k = hex2bin($key);

if ($binary_kid === false || $binary_k === false) {
    throw new Exception("Invalid hexadecimal input");
}

$encoded_kid = base64url_encode($binary_kid);
$encoded_k = base64url_encode($binary_k);

$response = [
    "keys" => [
        [
            "kty" => "oct",
            "k" => $encoded_k,
            "kid" => $encoded_kid
        ]
    ],
    "type" => "temporary"
];

header('Content-Type: application/json');
file_put_contents($cacheFile, json_encode($response));
echo json_encode($response);
