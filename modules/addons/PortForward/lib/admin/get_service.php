<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function($data){
    $s = Capsule::table("mod_PortForward_Services")->get();
    
    
    foreach($s as $val)
    {
    //根据ServiceID获取客户的UID信息以及邮箱
    $val->uid = Capsule::table("tblhosting")->where('id',$val->serviceid)->first()->userid; 
    $val->useremail = Capsule::table("tblclients")->where('id',$val->uid)->first()->email;
    $val->nextduedate = Capsule::table("tblhosting")->where('id',$val->serviceid)->first()->nextduedate; 
    }
    if (empty($s)){
        return [ "result" => "success" , "data" => []];
    }

    return [ "result" => "success" , "data" => $s];
};

