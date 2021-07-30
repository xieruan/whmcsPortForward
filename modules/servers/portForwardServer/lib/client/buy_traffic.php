<?php
use Illuminate\Database\Capsule\Manager as Capsule;


return function($data){
    if (!isset($data["serviceid"]) && !isset($data["traffic"]) && !is_numeric($data["traffic"])){
        return [ "result" => "error" , "error" => "参数不合法"];
    }
	//Check domainstatus
	$status = Capsule::table('tblhosting')->where('id', $data["serviceid"])->first()->domainstatus;
	$acceptableStatus = ['Active', 'Suspended', 'Pending'];
	if (!in_array($status, $acceptableStatus)) {
		return [ "result" => "error" , "error" => "服务状态异常，请续费或联系技术支持"];
	}
	
    $price = _get_PortForward_setting("traffic_price");
    if (empty($price)) {
        return [ "result" => "error" , "error" => "管理员未开启此功能，请联系技术支持"];
    }
    $total = $price * $data["traffic"];

    $invoice_exist = Capsule::table('mod_PortForward_Services')->where('serviceid', $data["serviceid"])->first()->invoiceid;
    if (empty(trim($invoice_exist))) {
        $userid = Capsule::table('tblhosting')->where('id', $data["serviceid"])->first()->userid;
        $sid = $data["serviceid"];
        $data = array(
            'userid' => $userid,
            'status' => 'Unpaid',
            'sendinvoice' => '1',
            'date' => date("Y-m-d"),
            'duedate' => date('Y-m-d',strtotime('+1 day')),
            'itemdescription1' => 'PortForward Traffic for #'.$data["serviceid"].' '.$data["traffic"].'G',
            'itemamount1' => $total,
            'itemtaxed1' => '0',
        );
        $results = localAPI('CreateInvoice', $data);
        if ($results['result'] == 'success') {
            $invoiceid = $results['invoiceid'];
            Capsule::table('mod_PortForward_Services')->where('serviceid', $sid)->update(["invoiceid" => $invoiceid]);
        } else {
            return ["result" => "error" , "error" => "账单创建失败，请联系技术支持"];
        }
    } else {
        return ["result" => "error" , "error" => "账单 #".$invoice_exist." 已存在，请<a href=\"viewinvoice.php?id=".$invoice_exist."\" target=\"_blank\">点我</a>前往支付"];
    }

    return ["result" => "success" , "invoiceid" => $invoiceid];
};