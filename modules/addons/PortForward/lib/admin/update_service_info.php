<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    if (!isset($data["traffic_all"])){
        return ["result" => "error" , "error" => "参数错误 , 请重试"];
    }

    if (!Capsule::table("mod_PortForward_Services")->where("serviceid", $data["serviceid"])->exists()){
        return ["result" => "error" , "error" => "服务不存在 , 请重试"];
    }

    if ($data["traffic_all"]) {
        Capsule::table("mod_PortForward_Services")->where("serviceid", $data["serviceid"])->update(["traffic_all" => $data["traffic_all"]]);
    }

    if ($data["speedlimit"]) {
        Capsule::table("mod_PortForward_Services")->where("serviceid", $data["serviceid"])->update(["bandwidth" => $data["speedlimit"]]);
        Capsule::table("mod_PortForward_PortInfo")->where("serviceid", $data["serviceid"])->update(["bandwidth" => $data["speedlimit"]]);
    }
    
    return ["result" => "success"];

};