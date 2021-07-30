<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!isset($_GET["act"])){
    die("Need action");
}

$access_levels = [];

    $access_levels[] = 'client';


foreach ($access_levels as $access_level){
    if (file_exists(__DIR__ . "/lib/{$access_level}/{$_GET['act']}.php")){
        $api_path = __DIR__ . "/lib/{$access_level}/{$_GET['act']}.php";
        break;
    }
}

if ($api_path){

    header("Content-Type:text/json; charset=utf-8");

    $api = require $api_path;

    $data = $_REQUEST;
    $data['serviceid'] = $params['serviceid'];
    unset($data["token"]);
    unset($data["act"]);
    unset($data["ajax"]);
    exit(json_encode($api($data)));
}
die("Action not exists");