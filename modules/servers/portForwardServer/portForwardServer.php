<?php

use Illuminate\Database\Capsule\Manager as Capsule;
//By Jiuling

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
//require_once __DIR__ . "/../../addons/PortForward/lib/helper/get_extra_port.php";
require_once __DIR__ . "/../../addons/PortForward/lib/init.php";

function portForwardServer_MetaData()
{
    return array(
        'DisplayName' => 'PortForward',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
    );
}

function portForwardServer_ConfigOptions()
{
    return array(
        'Traffic' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '1024',
            'Description' => '流量限制(MB)',
        ),
        'Node' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'null',
            'Description' => '填写节点ID，用|隔开,可组合两个节点转发，节点中用英文逗号隔开，其中排在前面的节点为用户入口节点，例如 |7,8|7',
        ),
        'Bandwidth' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '50',
            'Description' => '带宽限制(Mbps)',
        ),
        '通知消息' => array(
            'Type' => 'textarea',
            'Rows' => '3',
            'Cols' => '50',
            'Description' => "展示给客户的通知消息,可使用html"
        )
    );
}

function portForwardServer_CreateAccount(array $params)
{
    try {
        $serviceid = $params['serviceid'];
        $traffic = $params['configoption1'];
        $nodes = $params['configoption2'];
        $bandwidth = $params['configoption3'];
        $tmp = require __DIR__ . "/lib/helper/get_extra_bandwidth.php";
        $extra_bandwidth = $tmp(['serviceid' => $params["serviceid"]]);
        $bandwidth = $bandwidth + $extra_bandwidth;
        if (empty(Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->where('status', 'enabled')->first())) {
            Capsule::table('mod_PortForward_Services')->insert([
                "serviceid" => $serviceid,
                "node_ids" => $nodes,
                "traffic_all" => $traffic,
                "traffic_used" => '0',
                "bandwidth" => $bandwidth,
                "create_time" => time(),
                "update_time" => time(),
                "status" => "enabled",
            ]);
        } else {
            return 'This service has been opened, please do not repeat';
        }
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function portForwardServer_SuspendAccount(array $params)
{
    try {
        $serviceid = $params['serviceid'];

        Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->update(['status' => 'disabled']);
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'created')
            ->update([
                'status' => 'suspend',
                'update_time' => time(),
            ]);
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'changing')
            ->update([
                'status' => 'suspend',
                'update_time' => time(),
            ]);
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function portForwardServer_UnsuspendAccount(array $params)
{
    try {
        $serviceid = $params['serviceid'];

        Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->update(['status' => 'enabled']);
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'suspend')
            ->update([
                'status' => 'pending',
                'update_time' => time(),
            ]);
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function portForwardServer_TerminateAccount(array $params)
{
    try {
        $serviceid = $params['serviceid'];

        Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->delete();
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'created')
            ->update([
                'status' => 'deleting',
                'update_time' => time(),
            ]);
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'suspend')
            ->update([
                'status' => 'deleting',
                'update_time' => time(),
            ]);
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function portForwardServer_AdminCustomButtonArray()
{
    return array(
        "Reset Traffic" => "resetTraffic",
    );
}


function portForwardServer_resetTraffic(array $params)
{
    try {
        $serviceid = $params['serviceid'];

        Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->update(['traffic_used' => '0']);
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $serviceid)
            ->where('status', 'suspend')
            ->update([
                'status' => 'pending',
                'update_time' => time(),
            ]);
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function portForwardServer_setCustomfieldsValue($field, $value)
{
    $value = (string) $value;

    $res = Capsule::table('tblcustomfields')->where('relid', $this->pid)->where('fieldname', $field)->first();
    if ($res) {
        $fieldValue = Capsule::table('tblcustomfieldsvalues')->where('relid', $this->serviceid)->where('fieldid', $res->id)->first();
        if ($fieldValue) {
            if ($fieldValue->value !== $value) {
                Capsule::table('tblcustomfieldsvalues')
                    ->where('relid', $this->serviceid)
                    ->where('fieldid', $res->id)
                    ->update(
                        [
                            'value' => $value,
                        ]
                    );
            }
        } else {
            Capsule::table('tblcustomfieldsvalues')
                ->insert(
                    [
                        'relid'   => $this->serviceid,
                        'fieldid' => $res->id,
                        'value'   => $value,
                    ]
                );
        }
    }
}

function portForwardServer_ClientArea(array $params)
{
    try {
        if (isset($_GET["ajax"])) {
            require __DIR__ . "/ajax.php";
            exit();
        }
        $announcements = Capsule::table('tblproducts')->where('id', $params['pid'])->first()->configoption4;
        $productname = Capsule::table('tblproducts')->where('id', $params['pid'])->first()->name;
        return array(
            'tabOverviewReplacementTemplate' => 'overview.tpl',
            'templateVariables' => array(
                'serviceid' => $params['serviceid'],
                'announcements' => $announcements,
                'productname' => $productname,
            ),
        );
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
