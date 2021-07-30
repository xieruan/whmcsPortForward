<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $settings = require __DIR__ . "/../../setting.php";

    $a = new setting($settings);
    $a->SettingsTable = "mod_PortForward_setting";
    $_settings = [];
    foreach ($a->getSettingsWithValue() as $k => $v){
        $_settings[$k] = $v["value"];
    }
    $VerifyResult = $a->verifySettings($_settings);

    return $VerifyResult ? true : false;
};

