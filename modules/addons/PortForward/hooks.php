<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

add_hook('DailyCronJob', 1, function($vars) {
    $services = Capsule::table('mod_PortForward_Services')->get();
    $now = time();
    foreach ($services as $service) {
        $nextinvoicedate = strtotime(Capsule::table('tblhosting')->where('id', $service->serviceid)->first()->nextinvoicedate);
        $invoicedate = date('d', $nextinvoicedate);
        if (date('d', $now) == $invoicedate) {
            $id = Capsule::table('mod_PortForward_Services')->where('serviceid',  $service->serviceid)->first()->id;
            Capsule::table('mod_PortForward_Services')->where('id', $id)->update(['traffic_used' => '0', 'traffic_add' => '0' , 'invoiceid' => '', 'update_time' => time()]);
        }
    }
    
});

add_hook('DailyCronJob', 1, function($vars) {
    $ports = Capsule::table('mod_PortForward_PortInfo')->where('status', 'deleted')->get();
	$deleted_port_expiry_time = (Capsule::table('mod_PortForward_setting')->where('name', 'deleted_port_expiry_time')->first()->value)*86400;
    $now = time();
    foreach ($ports as $port) {
        if ($now - $port->update_time > $deleted_port_expiry_time) {
            Capsule::table('mod_PortForward_PortInfo')->where('id', $port->id)->delete();
        }
    }
    
});

add_hook('InvoicePaid', 2, function($vars) {
    $invoiceid = $vars['invoiceid'];
    $descr = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceid)->first()->description;
    if (strstr($descr, 'PortForward Traffic')) {
        $descr = explode(' ',$descr);
        $serviceid = str_replace('#', '', $descr['3']);
        $traffic = Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->first()->traffic_add;
        $traffic += str_replace('G', '', $descr['4'])*1024;
        Capsule::table('mod_PortForward_Services')->where('serviceid', $serviceid)->update(['traffic_add' => $traffic, 'invoiceid' => '', 'update_time' => time()]);
		$domainstatus = Capsule::table('tblhosting')->where('id', $serviceid)->first()->domainstatus;
		if ($domainstatus == 'Suspended') {
			//Unsuspend suspended service
                $postData = array(
                    'serviceid' => $serviceid,
                );
                localAPI('ModuleUnsuspend', $postData);
		}
    }
});

add_hook('ProductEdit', 2, function($vars) {
    if (!function_exists("_get_PortForward_setting")){
		function _get_PortForward_setting($name){
			if (!Capsule::table("mod_PortForward_setting")->where("name", $name)->exists()){
				return false;
			}
		return Capsule::table("mod_PortForward_setting")->where("name", $name)->first()->value;
		}
	}
	
	if ($vars['servertype'] == 'portForwardServer') {
		$sync = _get_PortForward_setting("node_id_sync_by_product");
		if ($sync == 'on') {
			$pid = $vars['pid'];
			$node_id = $vars['configoption2'];
			$services = Capsule::table('tblhosting')
			->where('packageid', $pid)
			->where(function ($query) {
				$query->where('domainstatus', 'Active')->orWhere('domainstatus', 'Suspended');
			})
			->get();
			foreach ($services as $service) {
				Capsule::table('mod_PortForward_Services')->where('serviceid', $service->id)->update(['node_ids' => $node_id, 'update_time' => time()]);
			}
		}
	}
});