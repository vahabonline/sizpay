<?php
/*
    This File Create by VahabOnline
    http://vahabonline.ir
    https://my.vahabonline.ir
    info@vahabonline.ir
    0937 465 5385
    011 5433 2064
*/

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . "/../vo_sizpay/nusoap.php";
$client = new NuSOAP_Client('https://rt.sizpay.ir/KimiaIPGRouteService.asmx?WSDL', 'wsdl');
$namespace='https://rt.sizpay.ir';
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}


    $invoiceid  = $_GET['invoiceid'];
    $amount  = $_GET['amount'];
    $Authority  = $_GET['Authority'];
    $invoiceid  = checkCbInvoiceID($invoiceid, $gatewayParams['name']);
    $Connection  = $gatewayParams['Connection'];


    $MerchantID = $_POST['MerchantID'];
    $TerminalID = $_POST['TerminalID'];
    $Token = $_POST['Token'];
    $SignData = '';
	$UserName = $gatewayParams['UserName'];
	$Password = $gatewayParams['Password'];


    $err = $client->getError();
    if ($err) {
        echo '<h2>GetTokenRequestButton Constructor error</h2><pre>' . $err . '</pre>';
        die();
    }
    $AppExtraInf	= '';
    $parameters = array(
        'MerchantID' => $MerchantID,
        'TerminalID' => $TerminalID,
        'Token' => $Token,
        'SignData' => $SignData,
        'UserName' => $UserName,
        'Password' => $Password
	);
    $result = $client->call('Confirm2', $parameters);
    if ($client->fault) {
        echo '<h2>Fault</h2><pre>';
        print_r($result);
        print_r(date("M,d,Y h:i:s A"));
        echo '</pre>';
        die();
    }else{


		$resultvo = $result['Confirm2Result'];
		$json = json_decode($resultvo);
		

		if($gatewayParams['Currencies'] == "Toman"){
			$amount = $amount/10;
		}

		$resOk = array("0", "00");
		if (in_array($json->ResCod, $resOk)){
			addInvoicePayment($invoiceid, $json->RefNo, $amount, $amount, $GATEWAY['name']);
			$status = "Successful";
		}else{
			$status = "Unsuccessful";
		}
		logTransaction($GATEWAY['name'], array('Get' => $_GET, 'Websevice' => (array) $resultO), $status);
		Header('Location: '.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceid);
		
    }

?>
