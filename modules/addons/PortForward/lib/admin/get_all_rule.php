<?php
use Illuminate\Database\Capsule\Manager as Capsule;

// fieldname  forward_nodeid

return function($data){
    function get_host_all_forward_port($hosingid){
        $all_rules = Capsule::table("mod_PortForward_PortInfo")
            ->where("mod_PortForward_PortInfo.serviceid", $hosingid)
            ->select(
                "id",
                "node_id",
                "nodeport AS public_port",
                "forwardip AS ip",
                "forwardport AS port",
                "method",
                "status"
            )
            ->get();
        return json_decode(json_encode($all_rules) , true);
    }
    $default_port_num = Capsule::table("mod_PortForward_setting")->where("name", "default_port_num")->first()->value;

    $all_hosts = Capsule::table("mod_PortForward_Services")->get();

    $all_hosts = json_decode(json_encode($all_hosts) , true);

    $custom_port_num_function = require __DIR__ . "/../helper/get_extra_port.php";

    foreach($all_hosts as $key => $host){

        $all_hosts[$key]["maximum_port"] = $default_port_num + $custom_port_num_function(["serviceid" => $host["serviceid"]]);
        $all_hosts[$key]["rules"] = get_host_all_forward_port($host["serviceid"]);
        unset($all_hosts[$key]["pid"]);
    }
    
    $nodes = Capsule::table("mod_PortForward_NodeInfo")->get();
    $nodes = json_decode(json_encode($nodes) , true);
    

    return(["result" => "success" , "data" => $all_hosts, "node" => $nodes]);
};