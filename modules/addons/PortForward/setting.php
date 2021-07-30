<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

return [
    "key" => [
        "displayname" => "通讯密钥",
        "type" => "input",
        "input_size" => "70",
        "require" => "true",
        "description" => "<br/>设置自定义API的通讯密钥, 其他人将不能通过该API进行存取"
    ],
    "cname_only" => [
        "displayname" => "仅显示CNAME",
        "type" => "input",
        "input_size" => "3",
        "description" => "如需节点仅为用户显示CNAME不显示IP，请务必填写on，否则请留空"
    ],
    "weloveidc_on" => [
        "displayname" => "SVM NAT端口检测",
        "type" => "input",
        "input_size" => "3",
        "description" => "如需与SVM NAT插件复用节点，请务必填写on，否则请留空"
    ],
	"node_id_sync_by_product" => [
        "displayname" => "产品同步",
        "type" => "input",
        "input_size" => "3",
        "description" => "如需更改产品节点ID后同步至已开通产品(处于Active,Suspended状态)，请填写on"
    ],
	"traffic_price" => [
        "displayname" => "流量加油包价格",
        "type" => "input",
        "input_size" => "5",
        "description" => "流量加油包价格, 单位为WHMCS默认货币(/GB)"
    ],
    "default_port_num" => [
        "displayname" => "默认可映射端口数量",
        "type" => "input",
        "input_size" => "5",
        "description" => "如无端口数量产品附加项, 则该设置项会代替产品附加项",
        "php_filter" => FILTER_VALIDATE_INT
    ],
    "deleted_port_expiry_time" => [
        "displayname" => "已删除端口记录过期时间",
        "type" => "input",
        "input_size" => "1",
        "description" => "单位为天 如无设置将不会删除已经被删除的端口的记录, 保留已删除端口的记录将有利于精确定位到客户",
        "php_filter" => FILTER_VALIDATE_INT
    ],
    "publicport_min" => [
        "displayname" => "对外端口最低位",
        "type" => "input",
        "input_size" => "5",
        "description" => "如设置 10000 则 10000 以下端口将被拒绝分配",
        "filter" => function($data){
            if (empty($data)) return true;
            if ($data <= 0 || $data > 65535){
                return false;
            }
            return true;
        }
    ],
    "publicport_blacklist" => [
        "displayname" => "对外端口黑名单",
        "type" => "input",
        "input_size" => 70,
        "description" => "拒绝为该列端口中创建端口映射(节点的服务端口), 多个端口用英文逗号,分割, 建议排除SSH端口以及WEB端口",
        "regex" => '/^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/m'
    ],
    "forwardport_blacklist" => [
        "displayname" => "转发端口黑名单",
        "type" => "input",
        "input_size" => 70,
        "description" => "拒绝转发端口列表(客户的服务端口), 多个端口用英文逗号,分割,建议排除http端口以及https端口",
        "regex" => '/^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/m'
    ],
];