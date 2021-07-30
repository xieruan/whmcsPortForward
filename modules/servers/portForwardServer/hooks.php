<?php

use WHMCS\Database\Capsule;


add_hook('ServiceDelete', 1, function ($vars) {
    try {
        Capsule::table('mod_PortForward_PortInfo')
            ->where('serviceid', $vars['serviceid'])
            ->update([
                'status' => 'deleting',
                'update_time' => time(),
            ]);
        Capsule::table('mod_PortForward_Services')
            ->where('serviceid', $vars['serviceid'])
            ->delete();
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
});


add_hook('AfterCronJob', 1, function ($vars) {
    try {
        portForwardServer_UpdateDDNS();
    } catch (Exception $e) {
    }
});

function portForwardServer_UpdateDDNS()
{
    try {
        $rules = Capsule::table("mod_PortForward_PortInfo")->where("forwarddomain", '<>', "forwardip")->where("status", 'created')->get();
        foreach ($rules as $rule) {
            $newip = gethostbyname($rule->forwarddomain);
            if ($newip != $rule->forwardip) {
                Capsule::table("mod_PortForward_PortInfo")->where("id", $rule->id)->update([
                    'status' => 'changing',
                    'update_time' => '114514',
                    'forwardip' => $newip,
                    'oldforwardip' => $rule->forwardip,
                ]);
            }
        }
    } catch (Exception $e) {
        logModuleCall(
            'portForwardServer',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}