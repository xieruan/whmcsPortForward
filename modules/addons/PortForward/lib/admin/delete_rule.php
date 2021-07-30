<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    if (!isset($data["ruleid"])){
        return ["result" => "error" , "error" => "参数错误 , 请重试"];
    }

    preg_match("/^(?!:\/\/)(?!.{256,})(([a-z0-9][a-z0-9_-]*?)|([a-z0-9][a-z0-9_-]*?\.)+?[a-z]{2,6}?)$/i", $data["cname"], $match);

    if (!Capsule::table("mod_PortForward_PortInfo")->where("id", $data["ruleid"])->exists()){
        return ["result" => "error" , "error" => "规则不存在 , 请重试"];
    }

    Capsule::table("mod_PortForward_PortInfo")->where('id',$data["ruleid"])->update(['status' => "deleting"]);
    return ["result" => "success"];
};