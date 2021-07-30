<?php

namespace PortForward;

echo " PortForward Node v1.21";
echo "\n Loading...\n";
require __DIR__ . "/init.php";
require __DIR__ . "/config.php";

use \PortForward\MethodDispatcher as MethodDispatcher;
use \PortForward\TrafficCounter as TrafficCounter;
use \PortForward\Base as Base;

if (PHP_SAPI != "cli") {
    exit('\n Run under cli!');
}
$base = new Base();
if (!isset($token)) {
    echo "\n Generating token...";
    $token = $base->rand_str();
    file_put_contents('/usr/local/PortForward/slave/config.php', PHP_EOL . '$token = \'' . $token . '\'; //token', FILE_APPEND | LOCK_EX);
    echo "\n Done";
}

$ip = trim($base->run("curl -4 'ip.sb'"));
echo "\n Your Server's Public IP: " . $ip;
echo "\n Geting Port Info...\n\n";
$data = [
    'key' => $key,
    'token' => $token,
    'action' => 'getport',
    'ip' => $ip,
    '0ba7yh8J' => 'aloIJ952xJ',
];

$port_data = $base->Post($url, $data);
if (empty($port_data)) {
    exit();
}
$port_data = json_decode($port_data, true);
if ($port_data['status'] == 'failed') {
    exit("\n Error!\n Message:" . $port_data['message']);
}

$port_data = $port_data['data'];
$Dispatch = new MethodDispatcher();
$TFCounter = new TrafficCounter();
$counter = [
    'new' => 0,
    'exists' => 0,
    'del' => 0,
    'changing' => 0,
    'updatestatus' => 0,
];
$file = __DIR__ . "/data/service.json";
$s = [];


foreach ($port_data as $port_curr) {
    $port_curr['nodebandwidth'] = $node_bw_max;
    $port_curr['sourceip'] = $sourceip;
    $port_curr['nic'] = $nic;
    $port_curr['burst'] = $burst;
    if ($port_curr['status'] == 'pending' or $port_curr['status'] == 'created') // changing should delete first
    {
        $s[$port_curr['serviceid']]['port'][] = $port_curr['nodeport'];
        if ($port_curr['method'] == 'iptables' || $port_curr['method'] == 'tinymapper' ) {
            if (!$Dispatch->Dispatch($port_curr['method'], 'checkRepeat', $port_curr)) {
                $Dispatch->Dispatch($port_curr['method'], 'create', $port_curr);
                $TFCounter->create_traffic($port_curr);
                $counter['new']++;
            } else {
                $counter['exists']++;
            }
        } else {
            $Dispatch->Dispatch($port_curr['method'], 'create', $port_curr);
            $TFCounter->create_traffic($port_curr);
            $counter['new']++;
        }
    } elseif ($port_curr['status'] == 'deleting' || $port_curr['status'] == 'suspend') {
        if ($Dispatch->Dispatch($port_curr['method'], 'checkRepeat', $port_curr)) {
            $Dispatch->Dispatch($port_curr['method'], 'delete', $port_curr);
            $TFCounter->delete_traffic($port_curr);
            $counter['del']++;
        }
    } elseif ($port_curr['status'] == 'changing') {
        $s[$port_curr['serviceid']]['port'][] = $port_curr['nodeport'];
        $resolddelete = $Dispatch->Dispatch($port_curr['method'], 'olddelete', $port_curr);
        $counter['changing']++;
        if ($resolddelete == true) {
            $TFCounter->delete_oldtraffic($port_curr);
            $data = [
                'key' => $key,
                'action' => 'updatestatus',
                'node_id' => $port_curr['node_id'],
                'nodeport' => $port_curr['nodeport'],
                'token' => $token,
                '0ba7yh8J' => 'aloIJ952xJ',
            ];
            $base->Post($url, $data);
            $counter['updatestatus']++;
        }
    }
}

file_put_contents($file, json_encode($s));
echo "\n Port info saved.";
echo "\n 任务执行完毕!";
echo "\n 本次添加的规则:" . $counter['new'];
echo "\n 节点存在的规则:" . $counter['exists'];
echo "\n 本次删除的规则:" . $counter['del'];
echo "\n 正在更新的规则:" . $counter['changing'];
echo "\n 更新状态的规则:" . $counter['updatestatus'] . "\n";