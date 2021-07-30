<?php

if ($_POST['0ba7yh8J'] != 'aloIJ952xJ') {
    exit(json_encode([
        'status' => 'failed',
        'message' => 'Permission Denied.',
    ]));
}
require __DIR__ . "/../../../init.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$key = $_POST['key'];

if ($key != Capsule::table('mod_PortForward_setting')->where('name', 'key')->first()->value) {
    exit(json_encode([
        'status' => 'failed',
        'message' => 'Permission Denied.',
    ]));
}
$token = $_POST['token'];
$action = $_POST['action'];

switch ($action) {
    case 'getport':
        $node = Capsule::table('mod_PortForward_NodeInfo')->where('token', $token)->first();
        if (empty($node)) {
            $data = [
                'groupname' => 'default',
                'cname' => '',
                'token' => $token,
                'graph' => '',
                'remoteip' => $_POST['ip'],
                'create_time' => time(),
                'status' => 'enabled',
            ];
            Capsule::table('mod_PortForward_NodeInfo')->insert($data);
            $node = Capsule::table('mod_PortForward_NodeInfo')->where('token', $token)->first();
        }
        if ($node->status == 'disabled') {
            exit(json_encode([
                'status' => 'failed',
                'message' => 'Node has been disabled by master.',
            ]));
        }
        $node_id = $node->id;
        //New IP
        Capsule::table('mod_PortForward_NodeInfo')->where('id', $node_id)->update(['remoteip' => $_POST['ip'],]);

        $port_all = Capsule::table('mod_PortForward_PortInfo')->where("node_id", "LIKE", "%" . $node_id . "%")->where('status', '<>', 'deleted')->get();
        $tmp = [];

        foreach ($port_all as $port) {
            $_tmp_node = explode(",", $port->node_id);
            if (sizeof($_tmp_node) == '1') {
                if ($port->method != 'socat') {
                    $port->multi = 0;
                }
                array_push($tmp, $port);
            } else if (sizeof($_tmp_node) == '2') {
                if ($_tmp_node['0'] == $node_id) {
                    // user => node 1 => node 2 => destination
                    $node_2 = Capsule::table('mod_PortForward_NodeInfo')->where("id", $_tmp_node['1'])->first();
                    if ($port->method != 'socat') {
                        $port->multi = 1;
                        $port->dest_ip = $port->forwardip;
                        $port->dest_port = $port->forwardport;
                    }
                    $port->forwardip = $node_2->remoteip;
                    $port->forwardport = $port->nodeport;
                    array_push($tmp, $port);
                } else if ($_tmp_node['1'] == $node_id) {
                    // node 2 
                    if ($port->method != 'socat') {
                        $port->multi = 2;
                    }
                    array_push($tmp, $port);
                }
            }
        }
        Capsule::table('mod_PortForward_PortInfo')->where('node_id', "LIKE", '%' . $node_id . '%')->where('status', 'pending')->update(['status'  => 'created', 'update_time' => time()]);
        Capsule::table('mod_PortForward_PortInfo')->where('node_id', "LIKE", '%' . $node_id . '%')->where('status', 'deleting')->update(['status' => 'deleted', 'update_time' => time()]);
        exit(json_encode([
            'status' => 'success',
            'data' => $tmp,
            'message' => 'success',
        ]));
        break;
    case 'uploadbw':
        $data = $_POST['data'];
        foreach ($data as $serviceid => $service) {
            $time = time();
            $tmp = Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->first();
            $traffic_all = $tmp->traffic_all + $tmp->traffic_add;
            $traffic = $tmp->traffic_used;
            $traffic = $traffic + $service['traffic'];
            Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->update(['traffic_used' => $traffic]);
            Capsule::table('mod_PortForward_NodeInfo')->where('token', $token)->update(['last_online_time' => $time]);
            if ($traffic >= $traffic_all) {
                $command = 'ModuleSuspend';
                $postData = array(
                    'serviceid' => $serviceid,
                    'suspendreason' => 'Out of traffic',
                );
                $results = localAPI($command, $postData);
            }
        }
        exit(json_encode([
            'status' => 'success',
            'message' => 'success',
        ]));
        break;
    case 'updatestatus':
        try {
        Capsule::table('mod_PortForward_PortInfo')->where('node_id',$_POST['node_id'])->where('nodeport',$_POST['nodeport'])->where('update_time','114514')->where('status','changing')->update(['status' => 'pending']);
        } catch (Exception $e) {
        }
        break;
    default:
        exit(json_encode([
            'status' => 'failed',
            'message' => 'Unknown action!',
        ]));
        break;
}