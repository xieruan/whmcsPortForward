<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $packageid = Capsule::table("tblhosting")->where("tblhosting.id", $data["serviceid"])->first()->packageid;

    $custom_extra_bandwidth = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
        ->where("tblproductconfiglinks.pid", $packageid)
        ->where("tblproductconfigoptions.optionname", "LIKE", "Extra Forward Bandwidth|%")
        ->exists();

    if ($custom_extra_bandwidth){
        $configaddon_id = Capsule::table("tblproductconfiglinks")->join("tblproductconfigoptions", "tblproductconfiglinks.gid", '=', "tblproductconfigoptions.gid")
            ->where("tblproductconfiglinks.pid", $packageid)
            ->where("tblproductconfigoptions.optionname", "LIKE", "Extra Forward Bandwidth|%")
            ->first()
            ->id;

        if (Capsule::table("tblhostingconfigoptions")->where("relid", $data["serviceid"])->where("configid", $configaddon_id)->exists()){
            $custom_extra_bandwidth = Capsule::table("tblhostingconfigoptions")
                ->where("relid", $data["serviceid"])
                ->where("configid", $configaddon_id)
                ->first()
                ->qty;

            return (int) $custom_extra_bandwidth;
        }
    }
};