<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $nodes = Capsule::table("mod_PortForward_NodeInfo")->get();

    if (empty($nodes)){
        return [ "result" => "success" , "node" => []];
    }

    return [ "result" => "success" , "node" => $nodes];
};