<?php

use Illuminate\Database\Capsule\Manager as Capsule;

function bwFormat($num)
{
    $num /= pow(1024, 1);
    return round($num, 0);
}


return function ($data) {
    $userid = $data["uid"] ? $data["uid"] : $_SESSION["uid"];

    if (!isset($data["serviceid"])) {
        return ["result" => "error", "error" => "参数不合法"];
    }

    if (!function_exists("get_host_all_forward_port")) {
        function get_host_all_forward_port($serviceid)
        {
            $all_rules = Capsule::table("mod_PortForward_PortInfo")
                ->where("serviceid", $serviceid)
                ->where("status", "<>", "deleted")
                ->select(
                    "id",
                    "node_id",
                    "nodeport",
                    "forwardport",
                    "forwarddomain",
                    "forwardip",
                    "method",
                    "bandwidth",
                    "status"
                )
                ->get();
            $tmp = [];
            foreach ($all_rules as $rules) {
                $tmp1 = explode(",", $rules->node_id);
                if (empty($tmp1['1'])) {
                    $tmp2 = Capsule::table('mod_PortForward_NodeInfo')->where('id', $tmp1['0'])->first();
                    $tmp[$tmp1['0']]['name'] = $tmp2->name;
                    $tmp[$tmp1['0']]['cname'] = $tmp2->cname;
                    $tmp[$tmp1['0']]['remoteip'] = $tmp2->remoteip;
                } else {
                    $tmp2 = Capsule::table('mod_PortForward_NodeInfo')->where('id', $tmp1['0'])->first();
                    $tmp3 = Capsule::table('mod_PortForward_NodeInfo')->where('id', $tmp1['1'])->first()->name;
                    $tmp[$tmp1['0']]['name'] = $tmp2->name . '->' . $tmp3;
                    $tmp[$tmp1['0']]['cname'] = $tmp2->cname;
                    $tmp[$tmp1['0']]['remoteip'] = $tmp2->remoteip;
                }
                $rules->nodename = $tmp[$tmp1['0']]['name'];
                $rules->cname = $tmp[$tmp1['0']]['cname'];
                $rules->remoteip = $tmp[$tmp1['0']]['remoteip'];
                switch ($rules->method) {
                    case "iptables":
                        $rules->method = 'iptables';
                        break;
                    case "brook":
                        $rules->method = 'Brook';
                        break;
                    case "tinymapper":
                        $rules->method = 'TinyMapper';
                        break;
                    case "gost":
                        $rules->method = 'Gost';
                        break;
                    case "realm":
                        $rules->method = 'Realm';
                        break;
                    case "ehco":
                        $rules->method = 'Ehco';
                        break;
                    default:
                        $rules->method = '未知';
                        break;
                }
            }
            unset($tmp);
            unset($tmp1);
            unset($tmp2);
            return json_decode(json_encode($all_rules), true);
        }
    }

    $base_port_num = Capsule::table("mod_PortForward_setting")->where("mod_PortForward_setting.name", 'default_port_num')->first()->value;
    $tmp = require __DIR__ . "/../helper/get_extra_port.php";
    $maximum_port =(int) $tmp(['serviceid' => $data["serviceid"]]) + $base_port_num;

    $cname_only = _get_PortForward_setting("cname_only");
    $rules = get_host_all_forward_port($data["serviceid"]);
    foreach ($rules as $k => $v) {
        if ($cname_only == "on") {
            $rules[$k]["remoteip"] = $rules[$k]["cname"];
            unset($rules[$k]["cname"]);
        }
    }
    $traffic = Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first();
    $traffic_all = $traffic->traffic_all + $traffic->traffic_add;
    $tmp = [];
    foreach (explode('|', $traffic->node_ids) as $nodeid) {
        $nodeids = explode(',', $nodeid);
        if (!isset($nodeids['1'])) {
            $a = Capsule::table('mod_PortForward_NodeInfo')->where('id', $nodeid)->first();
            $tmp[] = [
                'node_id' => $nodeids,
                'node_name' => $a->name, //后期改进数据库查询占用
                'bl' => $a->magnification,
            ];
        } else {
            $a = Capsule::table('mod_PortForward_NodeInfo')->where('id', $nodeids['0'])->first();
            $b = Capsule::table('mod_PortForward_NodeInfo')->where('id', $nodeids['1'])->first();
            $tmp[] = [
                'node_id' => $nodeids,
                'node_name' => $a->name . '->' . $b->name,
                'bl' => (int)$a->magnification + (int)$b->magnification,
                //后期改进数据库查询占用
            ];
        }
    }


    return ["result" => "success", "maximum_port" => $maximum_port, "rules" => $rules, "traffic_used" => bwFormat($traffic->traffic_used), "traffic_all" => bwFormat($traffic_all), 'nodes' => $tmp, 'auth' => 'Jiuling'];
};
