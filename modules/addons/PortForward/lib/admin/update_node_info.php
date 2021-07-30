<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    if (!isset($data["nodeid"]) || !isset($data["cname"]) || !isset($data["name"])){
        return ["result" => "error" , "error" => "参数错误 , 请重试"];
    }

    preg_match("/^(?!:\/\/)(?!.{256,})(([a-z0-9][a-z0-9_-]*?)|([a-z0-9][a-z0-9_-]*?\.)+?[a-z]{2,6}?)$/i", $data["cname"], $match);

    if (empty($match)){
        return ["result" => "error" , "error" => "CNAME格式错误 , 请重试"];
    }

    if (!Capsule::table("mod_PortForward_NodeInfo")->where("id", $data["nodeid"])->exists()){
        return ["result" => "error" , "error" => "节点不存在 , 请重试"];
    }

    Capsule::table("mod_PortForward_NodeInfo")->where("id", $data["nodeid"])->update(["cname" => $data["cname"], "name" => $data["name"]]);
    
    if ($data["portrange"]) {
    Capsule::table("mod_PortForward_NodeInfo")->where("id", $data["nodeid"])->update(["portrange" => $data["portrange"]]); 
    }
    return ["result" => "success"];
};