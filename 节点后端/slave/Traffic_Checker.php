<?php
namespace PortForward;

require __DIR__ . "/init.php";
require __DIR__ . "/config.php";

use \PortForward\TrafficCounter as TrafficCounter;
use \PortForward\Base as Base;
echo " PortForward Node v1.21";
echo "\n Collecting data...";

$TFCounter = new TrafficCounter();
$cron = $TFCounter->cron($url,$key,$magnification,$token);
$cron = json_decode($cron,true);
if (empty($cron)) {
	exit();
}
if ($cron['status'] = 'success')
{
    echo "\n Traffic data sync success.\n";
} else {
    echo "\n Traffic data sync failed.\n Reason:".$cron['message']."\n";
}
unset($TFCounter);
