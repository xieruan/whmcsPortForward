<?php
use Illuminate\Database\Capsule\Manager as Capsule;


return function($data){
    if (!isset($data["ruleid"]) and !is_numeric($data["ruleid"])){
        return [ "result" => "error" , "error" => "参数不合法"];
    }

    if (!Capsule::table("mod_PortForward_PortInfo")->where("id", $data["ruleid"])->where("status", "<>", "deleted")->exists()){
        return ["result" => "error" , "error" => "规则不存在 , 请重试"];
    }

    Capsule::table("mod_PortForward_PortInfo")->where("id", $data["ruleid"])->update([
        "status" => "deleting",
        "update_time" => time()
    ]);

    return ["result" => "success"];
};