<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use Illuminate\Database\Capsule\Manager as Capsule;


return function($data){
    $packageid = Capsule::table("tblhosting")->where("tblhosting.id", $data["serviceid"])->first()->packageid;

    $custom_port_num = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
        ->where("tblproductconfiglinks.pid", $packageid)
        ->where("tblproductconfigoptions.optionname", "LIKE", "Forward Port|%")
        ->exists();

    if ($custom_port_num){
        $configaddon_id = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
            ->where("tblproductconfiglinks.pid", $packageid)
            ->where("tblproductconfigoptions.optionname", "LIKE", "Forward Port|%")
            ->first()
            ->id;

        if (Capsule::table("tblhostingconfigoptions")->where("relid", $data["serviceid"])->where("configid", $configaddon_id)->exists()){
            $option = Capsule::table("tblproductconfigoptionssub")->where("id", Capsule::table("tblhostingconfigoptions")
                ->where("relid", $data["serviceid"])
                ->where("configid", $configaddon_id)->first()->optionid)
                ->first()
                ->optionname;

            $custom_port_num = explode("|", $option)[0];
            return (int) $custom_port_num;
        }
    } else {
            $custom_port_num = Capsule::table("mod_PortForward_setting")->where("mod_PortForward_setting.name", 'default_port_num')->first()->value;
            return (int) $custom_port_num;
    }
};