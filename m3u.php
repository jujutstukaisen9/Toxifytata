<?php

error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

$currentUrl = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$parsedUrl = parse_url($currentUrl);
$scheme = $parsedUrl['scheme'] ?? 'http';
$host = $parsedUrl['host'] ?? '';
$path = dirname($parsedUrl['path'] ?? '');
$newUrl = "{$scheme}://{$host}{$path}";

$cache_dir = "_cache_/";
$cacheTime = 7200;
$cacheFile = $cache_dir . "TP.m3u";

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    readfile($cacheFile);
    exit;
}

$data = @file_get_contents("https://fox.toxic-gang.xyz/tata/channels");
if ($data === false) {
    echo 'Error fetching data.';
    exit;
}

$data = json_decode($data, true);

$inus_data = '#EXTM3U x-tvg-url="https://avkb.short.gy/tsepg.xml.gz"' . PHP_EOL . PHP_EOL;

foreach ($data['data'] as $entry) {
    $id = $entry['id'];
    $name = $entry['title'];
    $genre = $entry['genre'];
    $logo = $entry['logo'];
    $mpd = $entry['initialUrl'];
    $extension = pathinfo($mpd, PATHINFO_EXTENSION);
    $license_key_url = "$newUrl/$id.key";

    $inus_data .= '#EXTINF:-1 tvg-id="ts' . $id . '" tvg-logo="' . $logo . '" group-title="TOXICIFY x ' . $genre . '", ' . $name . PHP_EOL;
    $inus_data .= '#KODIPROP:inputstream=inputstream.adaptive' . PHP_EOL;
    $inus_data .= '#KODIPROP:inputstreamaddon=inputstream.adaptive' . PHP_EOL;
    $inus_data .= '#KODIPROP:inputstream.adaptive.manifest_type=' . $extension . PHP_EOL;
    $inus_data .= '#KODIPROP:inputstream.adaptive.license_type=clearkey' . PHP_EOL;
    $inus_data .= '#KODIPROP:inputstream.adaptive.license_key=' . $license_key_url . PHP_EOL;

    if ($extension === 'm3u8') {
        $inus_data .= $entry['initialUrl'] . PHP_EOL . PHP_EOL;
    } else {
        $mpd_url = $entry['initialUrl'];
        if (strpos($mpd_url, 'bpweb') !== false) {
            $mpd_url = "$newUrl/$id.mpd";
        }
        $inus_data .= $mpd_url . PHP_EOL . PHP_EOL;
    }
}

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

file_put_contents($cacheFile, $inus_data);
echo $inus_data;
