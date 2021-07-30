<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use Illuminate\Database\Capsule\Manager as Capsule;


function PortForward_config()
{
    return [
        "name" => "PortForward",
        "description" => "用于WHMCS销售端口转发服务的管理",
        "version" => 'v1.21',
        "author" => "Jiu and more",
    ];
}

function PortForward_activate()
{
    try {
        Capsule::schema()->create("mod_PortForward_setting",function($table){
            $table->increments('id');
            $table->string('name');
            $table->text('value');
        });
    } catch (\Exception $e) {
        return [
            'status'        =>  'error',
            'description'   =>  "无法创建表 'mod_PortForward_setting' : {$e->getMessage()}"
        ];
    }

    try {
        Capsule::schema()->create("mod_PortForward_NodeInfo",function($table){
            $table->increments('id');
            $table->string('groupname');
            $table->string('name');
            $table->string('cname');
            $table->string('graph');
            $table->string('magnification');
            $table->string('node_bw_max');
            $table->string('remoteip');
            $table->string('portrange');
            $table->string('token');
            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('last_online_time');
            $table->enum('status', ['enabled', 'disabled']);
        });

    } catch (\Exception $e) {
        return [
            'status'        =>  'error',
            'description'   =>  "无法创建表 'mod_PortForward_NodeInfo' : {$e->getMessage()}"
        ];
    }

    try {
    Capsule::schema()->create("mod_PortForward_Services",function($table){
        $table->increments('id');
        $table->string('serviceid');
        $table->string('node_ids');
        $table->string('traffic_all');
        $table->string('traffic_used');
		$table->string('traffic_add');
        $table->string('invoiceid');
        $table->string('bandwidth');
        $table->integer('create_time');
        $table->integer('update_time');
        $table->enum('status', ['enabled', 'disabled']);
    });
    } catch (\Exception $e) {
        return [
            'status'        =>  'error',
            'description'   =>  "无法创建表 'mod_PortForward_Services' : {$e->getMessage()}"
        ];
    }


    try {
        Capsule::schema()->create("mod_PortForward_PortInfo",function($table){
            $table->increments('id');
            $table->string('serviceid');
            $table->string('node_id');
            $table->string('nodeport');
            $table->string('forwardport');
            $table->string('forwarddomain');
            $table->string('forwardip');
            $table->string('oldforwardip');
            $table->string('bandwidth');
            $table->string('method');
            $table->integer('create_time');
            $table->integer('update_time');
            $table->enum('status', ['created', 'deleted', 'pending', 'deleting', 'changing', 'suspend']);
        });

    } catch (\Exception $e) {
        return [
            'status'        =>  'error',
            'description'   =>  "无法创建表 'mod_PortForward_PortInfo' : {$e->getMessage()}"
        ];
    }

}

function PortForward_deactivate() {
    PortForward_droptable();
}

function PortForward_droptable(){
    Capsule::schema()->dropIfExists('mod_PortForward_setting');
    Capsule::schema()->dropIfExists('mod_PortForward_NodeInfo');
    Capsule::schema()->dropIfExists('mod_PortForward_Services');
    Capsule::schema()->dropIfExists('mod_PortForward_PortInfo');
}

function PortForward_output($vars)
{
    require __DIR__ . "/lib/init.php";
    require __DIR__ . "/version.php";
    function PortForward_page($page){
        switch ($page){
            case "admin_header":
            case "admin_footer":
            case "port_management":
            case "node_management":
            case "service_management":
            case "setting":
            case "about":
            case "dashboard":
                if (file_exists(__DIR__ . "/templates/custom/admin/{$page}.html")){
                    return file_get_contents(__DIR__ . "/templates/custom/admin/{$page}.html");
                } else {
                    return file_get_contents(__DIR__ . "/templates/admin/{$page}.html");
                }  // 优先获取自定义模板

                break;
            default:
                if (file_exists(__DIR__ . "/templates/custom/admin/dashboard.html")){
                    return file_get_contents(__DIR__ . "/templates/custom/admin/dashboard.html");
                } else {
                    return file_get_contents(__DIR__ . "/templates/admin/dashboard.html");
                } ///默认打开首页
                break;

        }
    }

    $is_setting_invaild = require __DIR__ . "/lib/admin/is_setting_invaild.php";
    $is_setting_invaild = !$is_setting_invaild([]);

    if (isset($_GET["ajax"])){
        if (isset($_GET["page"])){
            if ($is_setting_invaild && $_GET["page"] != "setting"){
                exit(PortForward_page("setting"));
            }
            exit(PortForward_page($_GET["page"]));
        }
        require __DIR__ . "/ajax.php";
        exit();
    }

    $page = PortForward_page("admin_header");
    
    if ($is_setting_invaild && $_GET["page"] != "setting"){
        $page .= '<div id="portforward_body" class="container-fluid">' . PortForward_page("setting") . "</div>";
    } else {
        $page .= '<div id="portforward_body" class="container-fluid">' . PortForward_page($_GET["page"]) . "</div>";
    }

    $page .= PortForward_page("admin_footer");
    $page .= "<hr/><p style='float:right'>当前运行版本: {$PortForward_version}</p>";

    echo $page;
}

function PortForward_clientarea($vars)
{
    return false;
}