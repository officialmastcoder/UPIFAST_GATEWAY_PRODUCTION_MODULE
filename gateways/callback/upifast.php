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
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$response = array();
$response = $_POST;
$gatewaymodule = "upifast"; 
$GATEWAY = getGatewayVariables($gatewaymodule);
// Die if module is not active.
if (!$GATEWAY['type']) {
    die("UPIFAST Module Not Activated");
}

if(isset($response['status']) &&  $response['status'] != NULL){
    $array = array();
    $paramList = array();
    $secret = $GATEWAY['upifast_secret']; // Your Secret Key.
    $hash = $response['hash']; // Encrypted Hash / Generated Only SUCCESS Status.
    $checksum = $response['checksum'];
    if($status=="SUCCESS"){
        $gateway_auth_key = $GATEWAY['upifast_module_auth_token'];
        $paramList = array('secret'=>urlencode($secret),'checksum'=>urlencode($checksum),'hash'=>urlencode($hash),'gateway_auth_key'=>urlencode($gateway_auth_key));
         $url  ="https://apizone.in/api/v6/verifyChecksum.php?param_list=".urlencode(json_encode($paramList));
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
        	$apiResdata = json_decode($result,true);
	        if($apiResdata['status']==1){
	        $resdata = json_decode($apiResdata['param_list'],true);
	        $orderid = $resdata['orderId'];
	            if($resdata['txnStatus']=="TXN_SUCCESS"){
	                	$txnid_arr  = explode('_',$resdata['orderId']);
	                	$txnid = $txnid_arr[0];
	                	$txnid  = checkCbInvoiceID($txnid,'upifast');
	                	$upifast_trans_id = $resdata['orderId'];
	                	$amount=$resdata['txnAmount'];
	                	checkCbTransID($upifast_trans_id);
	                	$gatewayresult = "success";
	        			addInvoicePayment($txnid, $upifast_trans_id, $amount,'0', $gatewaymodule);
	        			logTransaction($GATEWAY["name"], $resdata, $resdata['RESPMSG']);
	        				$filename=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	        				$filename = str_replace("/modules/gateways/callback/upifast.php","/clientarea.php?action=invoices",$filename);
	                        header("Location: $filename");
	            }
	        }else{
	            $filename=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	            $filename = str_replace("/modules/gateways/callback/upifast.php","/clientarea.php?action=invoices",$filename);
	            header("Location: $filename");
	        }
	}else{
	    $filename=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	    $filename = str_replace("/modules/gateways/callback/upifast.php","/clientarea.php?action=invoices",$filename);
	    header("Location: $filename");
	}
    }else{
        $filename=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
            $filename = str_replace("/modules/gateways/callback/upifast.php","/clientarea.php?action=invoices",$filename);
        header("Location: $filename");
    }
}else{
	$returnResponse=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]";
	header("Location: $returnResponse");
}
?>
