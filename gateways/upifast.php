<?php
/**
 * UPIFAST PAYMENT MODULE FOR WHMCS
 * @author AHK WEB SOLUTIONS
 * @Website https://ahkwebsolutions.com/
 * @license GPL V.1 
 * @Disclaimer Please do not temper the code 
 * settings.
 *
 * @return array
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


function upifast_MetaData()
{
    return array(
        'DisplayName' => 'UPIFAST Gateway',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}
function upifast_config(){
    $configarray = array(
		"FriendlyName" => array("Type" => "System", "Value"=>"UPIFAST"),
		"upifast_txn_url" => array("FriendlyName" => "UPIFAST TXN URL", "Type" => "text", "Size" => "30", "Placeholder"=>"https://trustmyhost.in/order/paytm"),
		"upifast_token" => array("FriendlyName" => "UPIFAST Token", "Type" => "text", "Size" => "30", "Placeholder"=>"3343-3333-3333-3333"),
		"upifast_secret" => array("FriendlyName" => "UPIFAST SECRET", "Type" => "password", "Size" => "20", "Placeholder"=>"12345dfd"),
		"upifast_upi_id" => array("FriendlyName" => "UPIFAST UPI id", "Type" => "text", "Size" => "90","Placeholder"=>"paytmbusinessupi@paytm" ),
		"upifast_module_auth_token" => array("FriendlyName" => "UPIFAST Module Auth Token", "Type" => "password", "Size" => "90","Placeholder"=>"upifast_auth_key" ),
	);		
	return $configarray;
}

function upifast_link($params) {
	$upifast_txn_url = $params['upifast_txn_url'];
	$upifast_token=$params['upifast_token'];
	$upifast_secret= $params['upifast_secret'];
	$order_id = $params['invoiceid'].'_'.time();
	$upifast_upi_id= $params['upifast_upi_id'];
	$upifast_module_auth_token= $params['upifast_module_auth_token'];
	
	$phone = (strlen($params['clientdetails']['phonenumber'])==10) ? $params['clientdetails']['phonenumber'] : '9182717271';
	
	$amount = $params['amount']; 
	$email = $params['clientdetails']['email'];
	$callBackLink=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	$callBackLink=str_replace('cart.php', 'modules/gateways/callback/upifast_callback.php', $callBackLink);
	$callBackLink=str_replace('viewinvoice.php', 'modules/gateways/callback/upifast_callback.php', $callBackLink);
	
    $checkSum = "";
    $txnNote = "Hosting Payment";
       
    $paramList = array();
    $paramList["gateway_auth_key"] = $upifast_module_auth_token;
    $paramList["upiuid"] = $upifast_upi_id;
    $paramList["token"] = $upifast_token;
    $paramList["orderId"] = $order_id ;
    $paramList["txnAmount"] = $amount;
    $paramList["txnNote"] = $txnNote;
    $paramList["cust_Email"] = $email;  
    $paramList["cust_Mobile"] = $phone;
    $paramList["callback_url"] = $callBackLink;
    $paramList["upifast_secret"]= $upifast_secret;
	
	$url  ="https://apizone.in/api/v6/generateChecksum.php?param_list=".urlencode(json_encode($paramList));
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 0,
	    CURLOPT_FOLLOWLOCATION => true,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	));
	$result = curl_exec($curl);
	curl_close($curl);
	if($result!=''){
	    $resData = json_decode($result,true);
	    if($resData['status']==1){
		$checkSum = $resData['checksum'];
		$code='<form method="post" name="f1" action='. $upifast_txn_url .'>';
		foreach ($paramList as $key => $value) {
			$code.='<input type="hidden" name="'.$key.'" value="'.$value. '"/>';
		}
		$code.='<input type="hidden" name="checksum" value="'. $checkSum . '"/><input type="submit" value="Pay with UPIFAST" /></form><script type="text/javascript">
					document.f1.submit();
				</script>';
		return $code;
	    }else{
		$messageE = $resData['message'];
		$message = "<b style='color:red;'>".$messageE."</b>";
		return $message;
	    }
	}else{
	  $messageE = "UPIFAST MODULE API ERROR! Contact With Module Provider";
	  $message = "<b style='color:red;'>".$messageE."</b>";
	  return $message;
	}
    	
}
?>
