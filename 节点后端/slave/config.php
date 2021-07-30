<?php
$nic = 'eth0'; //主网卡名称
$url = 'https://example.com/modules/addons/PortForward/apicall.php';
$key = ''; //在WHMCS设置的key
$sourceip = ''; //注意，这里并不是外网IP，而是在 ifconfig 中主网卡的IP地址
$magnification = '1'; //流量倍率 默认为1,双向计算，如需单向计算，输入0.5
$node_bw_max = '100'; //mbps
$burst = 'false'; //带宽突发