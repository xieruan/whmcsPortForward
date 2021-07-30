<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $is_setting_invaild = require __DIR__ . "/is_setting_invaild.php";
    $is_setting_invaild = $is_setting_invaild([]);

    $settings = require __DIR__ . "/../../setting.php";

    $a = new setting($settings);
    $a->SettingsTable = "mod_PortForward_setting";

    return(["result" => "success" , "setting" => $a->getSettingsWithValue(), "vaild" => $is_setting_invaild]);
};