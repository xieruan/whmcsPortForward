<?php

use Illuminate\Database\Capsule\Manager as Capsule;


return function ($data) {
    if (!isset($data["serviceid"]) && !isset($data["dest_ip"]) && !isset($data["dest_port"]) && !isset($data["pub_port"]) && !isset($data['method']) && !isset($data['pub_node'])) {
        return ["result" => "error", "error" => "缺少参数"];
    }

    $rules = Capsule::table('mod_PortForward_PortInfo')->where('status', '<>', 'deleted')->where('serviceid', $data["serviceid"])->get();
    $base_port_num = Capsule::table("mod_PortForward_setting")->where("mod_PortForward_setting.name", 'default_port_num')->first()->value;
    $tmp = require __DIR__ . "/../helper/get_extra_port.php";
    $max_port_num = $tmp(['serviceid' => $data["serviceid"]]) + $base_port_num;

    if ($max_port_num <= count($rules)) {
        return ["result" => "error", "error" => "端口分配数量已达到最大限额"];
    }


    if ($data["dest_port"] <= 0 || $data["dest_port"] > 65535) {
        return ["result" => "error", "error" => "你的端口无效"];
    }

    if ($data["pub_port"] <= 0 || $data["pub_port"] > 65535) {
        return ["result" => "error", "error" => "转发端口无效"];
    }

    $is_domain = preg_match("/^(?!:\/\/)(?!.{256,})(([a-z0-9][a-z0-9_-]*?)|([a-z0-9][a-z0-9_-]*?\.)+?[a-z]{2,6}?)$/i", $data['dest_ip']);
    if (!filter_var(trim($data['dest_ip']), FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_IPV4)) {
        if (!$is_domain) {
            return ["result" => "error", "error" => "域名/IP 无效"];
        }
    }

    $blanklist_port = explode(",", _get_PortForward_setting("publicport_blacklist"));
    if (in_array($data["pub_port"], $blanklist_port)) {
        return ["result" => "error", "error" => "转发端口 【{$data["pub_port"]}】 已被管理员列入黑名单"];
    }

    $blacklist_forwardport = explode(",", _get_PortForward_setting("forwardport_blacklist"));
    if (in_array($data["dest_port"], $blacklist_forwardport)) {
        return ["result" => "error", "error" => "你的端口 【{$data["dest_port"]}】 已被管理员列入黑名单"];
    }

    $node_info = Capsule::table("mod_PortForward_NodeInfo")->where("id", $data['pub_node'])->first();

    if ($data['dest_ip'] == 'localhost' || $data['dest_ip'] == $node_info->cname || $data['dest_ip'] == $node_info->remoteip || $data['dest_ip'] == '127.0.0.1') {
        return ["result" => "error", "error" => $node_info->cname];
    }

    if (!($node_info->status = 'enabled')) {
        return ["result" => "error", "error" => "转发节点不存在或不可用"];
    }

    if ($node_info->portrange) {
        $portrange = explode('-', $node_info->portrange);
        if ($data["pub_port"] < $portrange['0'] || $data["pub_port"] > $portrange['1']) {
            return ["result" => "error", "error" => "转发端口无效，此节点的可用转发端口段为 【{$portrange['0']}-{$portrange['1']}】"];
        }
    } else {
        $public_port_min = _get_PortForward_setting("publicport_min");
        if ($public_port_min && $data["pub_port"] < $public_port_min) {
            return ["result" => "error", "error" => "转发端口必须大于 【{$public_port_min}】"];
        }
    }

    $node = explode(',', $data['pub_node']);
    foreach ($node as $i) {
        if (!is_numeric($i)) {
            return ["result" => "error", "error" => "节点ID不合法"];
        }
    }

    foreach ($node as $i) {
        $port_exist = Capsule::table("mod_PortForward_PortInfo")->where("status", "<>", "deleted")
            ->where("nodeport", $data["pub_port"])
            ->where("node_id", "LIKE", '%' . $i['0'] . '%')
            ->exists();
        if ($port_exist) {
            $port_exists = true;
            break;
        }
    }

    if ($port_exist) {
        $port_exists = true;
    }

    if ($port_exists) {
        return ["result" => "error", "error" => "转发端口 【{$data["pub_port"]}】 已被占用, 请更换其他转发端口"];
    }

    $checksimilarityrulesexist = Capsule::table("mod_PortForward_PortInfo")->where("node_id", $data['pub_node'])
        ->where("forwardport", $data['dest_port'])
        ->where("forwarddomain", $data['dest_ip'])
        ->where("status", '<>', 'deleted')
        ->exists();
    if ($checksimilarityrulesexist) {
        return ["result" => "error", "error" => "禁止在同一转发节点上重复转发相同服务"];
    }

    $weloveidc_on = _get_PortForward_setting("weloveidc_on");
    if ($weloveidc_on == 'on') {
        if (Capsule::hasTable('mod_weloveidc_solusvmnat_port')) {
            foreach ($node as $i) {
                $node_ip = Capsule::table('mod_PortForward_NodeInfo')->where('id', $i)->first()->remoteip;
                $weloveidc_exist =  Capsule::table("mod_weloveidc_solusvmnat_port")->where("status", "<>", "deleted")
                    ->where("public_port", $data["pub_port"])
                    ->where("node_id", Capsule::table("mod_weloveidc_solusvmnat_node")->where("interface_ip", $node_ip)->first()->id)
                    ->exists();
                if ($weloveidc_exist) {
                    $weloveidc_exists = true;
                    break;
                }
            }

            if ($weloveidc_exists) {
                return ["result" => "error", "error" => "转发端口 【{$data["pub_port"]}】 已被占用, 请更换其他转发端口"];
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

    if (is_ip($data["dest_ip"]) == true) {
        Capsule::table("mod_PortForward_PortInfo")->insert([
            "serviceid" => $data["serviceid"],
            "nodeport" => $data["pub_port"],
            "forwardport" => $data["dest_port"],
            "forwarddomain" => $data["dest_ip"],
            "forwardip" => $data["dest_ip"],
            "method" => $data["method"],
            "bandwidth" => Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first()->bandwidth,
            "node_id" => $data['pub_node'],
            "status" => "pending",
            "create_time" => time(),
            "update_time" => time(),
        ]);
        return ["result" => "success"];
    } else {
        $dest_ip = gethostbyname($data["dest_ip"]);
        Capsule::table("mod_PortForward_PortInfo")->insert([
            "serviceid" => $data["serviceid"],
            "nodeport" => $data["pub_port"],
            "forwardport" => $data["dest_port"],
            "forwarddomain" => $data["dest_ip"],
            "forwardip" => $dest_ip,
            "method" => $data["method"],
            "bandwidth" => Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first()->bandwidth,
            "node_id" => $data['pub_node'],
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
