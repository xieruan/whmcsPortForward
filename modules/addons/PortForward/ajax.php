<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!isset($_GET["action"])){
    die("Need action");
}

$access_levels = [];
if (isset($_SESSION["adminid"]) && strpos($_SERVER["SCRIPT_NAME"],"addonmodules.php") !== false){
    $access_levels[] = 'admin';
} elseif (isset($_SESSION["uid"]) && isset($_SESSION["upw"])){
    $access_levels[] = 'client';
}


foreach ($access_levels as $access_level){
    if (file_exists(__DIR__ . "/lib/{$access_level}/{$_GET['action']}.php")){
        $api_path = __DIR__ . "/lib/{$access_level}/{$_GET['action']}.php";
        break;
    }
}

if ($api_path){
    require_once __DIR__ . "/lib/init.php";

    header("Content-Type:text/json; charset=utf-8");

    $api = require $api_path;

    $data = $_POST ? $_POST : $_GET;
    unset($data["token"]);
    unset($data["module"]);
    unset($data["action"]);
    unset($data["ajax"]);
    exit(json_encode($api($data)));
}

die("Action not exists");