<?php

use Illuminate\Database\Capsule\Manager as Capsule;


return function ($data) {
    if (!isset($data["serviceid"]) && !isset($data["in_port"]) && !isset($data["out_port"]) && !isset($data["type"])) {
        return ["result" => "error", "error" => "参数缺失"];
    }

    $rules = Capsule::table('mod_PortForward_PortInfo')->where('status', '<>', 'deleted')->where('serviceid', $data["serviceid"])->get();
    $base_port_num = Capsule::table("mod_PortForward_setting")->where("mod_PortForward_setting.name", 'default_port_num')->first()->value;
    $tmp = require __DIR__ . "/../helper/get_extra_port.php";
    $max_port_num = $tmp(['serviceid' => $data["serviceid"]]) + $base_port_num;

    if ($max_port_num <= count($rules)) {
        return ["result" => "error", "error" => "端口分配已超过允许数量" . $max];
    }

    if (!($data["method"] == "method1" || $data["method"] == "method2" || $data["method"] == "method3")) {
        return ["result" => "error", "error" => "转发程序无效"];
    }

    if ($data["in_port"] <= 0 || $data["in_port"] > 65535) {
        return ["result" => "error", "error" => "目标端口无效"];
    }

    if ($data["out_port"] <= 0 || $data["out_port"] > 65535) {
        return ["result" => "error", "error" => "转发端口无效"];
    }

    $blanklist_port = explode(",", _get_PortForward_setting("publicport_blacklist"));
    if (in_array($data["out_port"], $blanklist_port)) {
        return ["result" => "error", "error" => "转发端口 【{$data["out_port"]}】 处于黑名单中"];
    }

    $blacklist_forwardport = explode(",", _get_PortForward_setting("forwardport_blacklist"));
    if (in_array($data["in_port"], $blacklist_forwardport)) {
        return ["result" => "error", "error" => "目标端口 【{$data["in_port"]}】 被禁止转发"];
    }

    $node_info = Capsule::table("mod_PortForward_NodeInfo")->where("id", $data['node_id'])->first();

    if (!($node_info->status = 'enabled')) {
        return ["result" => "error", "error" => "转发节点不存在或处于不可用状态"];
    }

    if ($node_info->portrange) {
        $portrange = explode('-', $node_info->portrange);
        if ($data["out_port"] <= $portrange['0'] || $data["out_port"] > $portrange['1']) {
            return ["result" => "error", "error" => "转发端口无效, 端口须在 【{$portrange['0']}-{$portrange['1']}】 端口段内"];
        }
    } else {
        $public_port_min = _get_PortForward_setting("publicport_min");
        if ($public_port_min && $data["out_port"] < $public_port_min) {
            return ["result" => "error", "error" => "转发端口需要大于 【{$public_port_min}】"];
        }
    }

    $checksimilarityrulesexist = Capsule::table("mod_PortForward_PortInfo")->where("node_id", $data['node_id'])
        ->where("forwardport", $data['in_port'])
        ->where("forwarddomain", $data['in_ip'])
        ->where("status", '<>', 'deleted')
        ->exists();
    if ($checksimilarityrulesexist) {
        return ["result" => "error", "error" => "禁止重复转发同一目标和端口"];
    }

    $node = explode(',', $data['node_id']);
    foreach ($node as $i) {
        if (!is_numeric($i)) {
            return ["result" => "error", "error" => "节点ID不合法"];
        }
    }

    foreach ($node as $i) {
        $port_exist = Capsule::table("mod_PortForward_PortInfo")->where("status", "<>", "deleted")
            ->where("nodeport", $data["out_port"])
            ->where("node_id", "LIKE", '%' . $i['0'] . '%')
            ->exists();
        if ($port_exist) {
            $port_exists = true;
            break;
        }
    }

    if ($port_exists) {
        return ["result" => "error", "error" => "转发端口 【{$data["out_port"]}】 已被占用, 请更换其他转发端口"];
    }

    $weloveidc_on = _get_PortForward_setting("weloveidc_on");
    if ($weloveidc_on == 'on') {
        if (Capsule::hasTable('mod_weloveidc_solusvmnat_port')) {
            foreach ($node as $i) {
                $node_ip = Capsule::table('mod_PortForward_NodeInfo')->where('id', $i)->first()->remoteip;
                $weloveidc_exist =  Capsule::table("mod_weloveidc_solusvmnat_port")->where("status", "<>", "deleted")
                    ->where("public_port", $data["out_port"])
                    ->where("node_id", Capsule::table("mod_weloveidc_solusvmnat_node")->where("interface_ip", $node_ip)->first()->id)
                    ->exists();
                if ($weloveidc_exist) {
                    $weloveidc_exists = true;
                    break;
                }
            }

            if ($weloveidc_exists) {
                return ["result" => "error", "error" => "转发端口 【{$data["out_port"]}】 已被占用, 请更换其他转发端口"];
            }
        }
    }

    switch ($data['method']) {
        case 'method1':
            $data['method'] = 'iptables';
            break;
        case 'method2':
            $data['method'] = 'brook';
            break;
        case 'method3':
            $data['method'] = 'tinymapper';
            break;
        case 'method4':
            $data['method'] = 'gost';
            break;
        case 'method5':
            $data['method'] = 'realm';
            break;
        case 'method6':
            $data['method'] = 'ehco';
            break;
        default:
            $data['method'] = 'iptables';
            break;
    }

    if (is_ip($data["in_ip"]) == true) {
        Capsule::table("mod_PortForward_PortInfo")->insert([
            "serviceid" => $data["serviceid"],
            "forwardport" => $data["in_port"],
            "nodeport" => $data["out_port"],
            "forwarddomain" => $data["in_ip"],
            "forwardip" => $data["in_ip"],
            "method" => $data["method"],
            "bandwidth" => Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first()->bandwidth,
            "node_id" => $data['node_id'],
            "status" => "pending",
            "create_time" => time(),
            "update_time" => time(),
        ]);

        return ["result" => "success"];
    } else {
        $in_ip = gethostbyname($data["in_ip"]);
        Capsule::table("mod_PortForward_PortInfo")->insert([
            "serviceid" => $data["serviceid"],
            "forwardport" => $data["in_port"],
            "nodeport" => $data["out_port"],
            "forwarddomain" => $data["in_ip"],
            "forwardip" => $in_ip,
            "method" => $data["method"],
            "bandwidth" => Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first()->bandwidth,
            "node_id" => $data['node_id'],
            "status" => "pending",
            "create_time" => time(),
            "update_time" => time(),
        ]);

        return ["result" => "success"];
    }
};

function is_ip($ip)
{
    $arr = explode('.', $ip);
    if (count($arr) != 4) {
        return false;
    } else {
        for ($i = 0; $i < 4; $i++) {
            if (($arr[$i] < 0) || ($arr[$i] > 255)) {
                return false;
            }
        }
    }
    return true;
}
