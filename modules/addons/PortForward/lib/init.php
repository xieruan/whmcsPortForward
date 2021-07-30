<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . "/helper/setting.class.php";


function _get_PortForward_setting($name){
    if (!Capsule::table("mod_PortForward_setting")->where("name", $name)->exists()){
        return false;
    }
    return Capsule::table("mod_PortForward_setting")->where("name", $name)->first()->value;
}
