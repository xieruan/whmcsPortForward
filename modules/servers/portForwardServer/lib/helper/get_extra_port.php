<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use Illuminate\Database\Capsule\Manager as Capsule;


return function($data){
    $packageid = Capsule::table("tblhosting")->where("tblhosting.id", $data["serviceid"])->first()->packageid;

    $custom_extra_port = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
        ->where("tblproductconfiglinks.pid", $packageid)
        ->where("tblproductconfigoptions.optionname", "LIKE", "Extra Forward Port|%")
        ->exists();

    if ($custom_extra_port){
        $configaddon_id = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
            ->where("tblproductconfiglinks.pid", $packageid)
            ->where("tblproductconfigoptions.optionname", "LIKE", "Extra Forward Port|%")
            ->first()
            ->id;

        if (Capsule::table("tblhostingconfigoptions")->where("relid", $data["serviceid"])->where("configid", $configaddon_id)->exists()){
            $custom_extra_port = Capsule::table("tblhostingconfigoptions")
                ->where("relid", $data["serviceid"])
                ->where("configid", $configaddon_id)
                ->first()
                ->qty;

            return (int) $custom_extra_port;
        }
    }
};