<?php
/*
	This File Create by VahabOnline
    http://vahabonline.ir
    https://my.vahabonline.ir
    info@vahabonline.ir
    0937 465 5385
    011 5433 2064
*/
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
function vo_sizpay_MetaData(){
    return array(
        'DisplayName' => 'سیزپی - وهاب آنلاین',
        'APIVersion' => '1.0',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}
function vo_sizpay_config() {
    $configarray = array(
        "FriendlyName" => array(
            "Type" => "System",
            "Value"=>"سیزپی - وهاب آنلاین"
        ),
        "UserName" => array(
            "FriendlyName" => "نام کاربری / کلید رمزنگاری",
            "Type" => "text",
        ),
        "Password" => array(
            "FriendlyName" => "رمز عبور / کلید رمزنگاری 2",
            "Type" => "text",
        ),
        "MerchantID" => array(
            "FriendlyName" => "شماره پذیرنده",
            "Type" => "text",
        ),
        "TerminalID" => array(
            "FriendlyName" => "شماره ترمینال",
            "Type" => "text",
        ),
        "Currencies" => array(
            "FriendlyName" => "واحد پولی",
            "Type" => "dropdown",
            "Options" => array(
                'Rial' => 'ریال',
                'Toman' => 'تومان',
            ),
        ),
        "TextBtn" => array(
            "FriendlyName" => "متن کلید پرداخت",
            "Type" => "text",
            "Default" => "پرداخت آنلاین",
            "Size" => "200",
        ),
        "BtnColor" => array(
            "FriendlyName" => "رنگ کلید",
            "Type" => "dropdown",
            "Options" => array(
                '' => 'خاکستری',
                'btn-default' => 'شیشه ای',
                'btn-primary' => 'آبی پررنگ',
                'btn-success' => 'سبز',
                'btn-info' => 'آبی کم رنگ',
                'btn-warning' => 'نارنجی',
                'btn-danger' => 'قرمز',
                'btn-link' => 'لینک ساده',
            ),
        ),
    );
    return $configarray;
}

function vo_sizpay_link($params) {
	
	$nusoap = __DIR__ . '/vo_sizpay/nusoap.php';
	require_once($nusoap);
	$client = new NuSOAP_Client('https://rt.sizpay.ir/KimiaIPGRouteService.asmx?WSDL', 'wsdl');
	$namespace='https://rt.sizpay.ir';
    $MerchantID = $params['MerchantID'];
    $TerminalID = $params['TerminalID'];
    $UserName = $params['UserName'];
    $Password = $params['Password'];
    $currencies = $params['Currencies'];
    $ConnectTo = $params['ConnectTo'];
    $connection = $params['Connection'];
    $TextBtn = $params['TextBtn'];
    $BtnColor = $params['BtnColor'];
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $Amount = str_replace('.00','',$params['amount']);
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];
    $systemurl = $params['systemurl'];
	$Description = "InvID : " . $invoiceid;
    $CallbackURL = $systemurl . 'modules/gateways/callback/vo_sizpay.php?invoiceid='. $invoiceid;


    if($currencies == "Toman"){
        $Amount = $Amount*10;
    }
	
	
	$parameters = array(
		'MerchantID' => $MerchantID,
		'TerminalID' => $TerminalID,
		'Amount' => $Amount,
		'DocDate' => '',
		'OrderID' => rand(10000, 99999),
		'ReturnURL' => $CallbackURL,
		'ExtraInf' => $Description,
		'InvoiceNo' => $invoiceid,
		'AppExtraInf' => '',
		'SignData' => '',
		'UserName' => $UserName,
		'Password' => $Password
	);
	$result = $client->call('GetToken2', $parameters);

		if ($client->fault) {
			echo '<h2>Fault call</h2><pre>';
			print_r($result);
			print_r(date("M,d,Y h:i:s A"));
			echo '</pre>';
			die();
		}
		else {
			$res = $result['GetToken2Result'];
			$json = json_decode($res);

            $err = $client->getError();
			if ($err) {
				 echo '<h2>Error</h2><pre>' . $err . '</pre>';
				 die();
			 }else{
                $resultStr = implode($result);
                $resultStr2 = json_decode($resultStr, true);
                $Token = $resultStr2["Token"];
                $ResCod = $resultStr2["ResCod"];
                $Message = $resultStr2["Message"];

                if ($ResCod == "0") {
					$code = '<form action="https://rt.sizpay.ir/Route/Payment" method="POST">
						<input type="hidden" class="TextTd" name="Token" value="'.$json->Token.'" />
						<input type="hidden" class="TextTd" name="MerchantID" value="'.$MerchantID.'" />
						<input type="hidden" class="TextTd" name="TerminalID" value="'.$TerminalID.'" />
						<input type="hidden" class="TextTd" name="SignData" value="" />
						<input type="submit" class="btn '.$BtnColor.'" value="'.$TextBtn.'" />
					</form>';
				}else{
					echo $Message;
				}
			}
		}
    return $code;
}

?>
