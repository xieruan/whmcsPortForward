<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $settings = require __DIR__ . "/../../setting.php";

    $a = new setting($settings);
    $settings = $a->GetSettings();
    $VerifyResult = $a->VerifySettings($data);
    $a->SettingsTable = "mod_PortForward_setting";

    if ($VerifyResult !== true){
        return $VerifyResult;
    }

    $a->updateSettings($data);

    return [ "result" => "success" ];
};
