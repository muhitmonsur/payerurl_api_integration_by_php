<?php
 
/**
 * Pyerurl will send a POST request to notify_url from payment request
 * Add this below code to your callback page
 */
 
/**
 * Payerurl API credentials
 */

$payerurl_public_key = 'your payerurl public key';  
$payerurl_secret_key = 'your payerurl secret key'; 
 
$headers = getallheaders();
$auth ="";

if ($headers === false || !array_key_exists('Authorization', $headers)) {
    /////////////  YOUR CODE HERE   ///////////////////////////
	
	$authStr_post = base64_decode($_POST['authStr']);
    $auth = explode(':', $authStr_post);

} else
{
	$authStr = str_replace('Bearer ', '', $headers['Authorization']);
	$authStr = base64_decode($authStr);
	$auth = explode(':', $authStr);
}
 



if ($payerurl_public_key != $auth[0]) {
    /////////////  YOUR CODE HERE   ///////////////////////////
    $data = ['status' => 2030, 'message' => 'Public key doesn\'t match'];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}


 
$GETDATA = [
    'order_id' => $_POST['order_id'],
    'ext_transaction_id' => $_POST['ext_transaction_id'],
    'transaction_id' => $_POST['transaction_id'],
    'status_code' => $_POST['status_code'],
    'note' => $_POST['note'],
    'confirm_rcv_amnt' => $_POST['confirm_rcv_amnt'],
    'confirm_rcv_amnt_curr' => $_POST['confirm_rcv_amnt_curr'],
    'coin_rcv_amnt' => $_POST['coin_rcv_amnt'],
    'coin_rcv_amnt_curr' => $_POST['coin_rcv_amnt_curr'],
    'txn_time' => $_POST['txn_time']
];
 
if (!isset($GETDATA['transaction_id']) || empty($GETDATA['transaction_id'])) {
    /////////////  YOUR CODE HERE   ///////////////////////////
    $data = ['status' => 2050, 'message' => "Transaction ID not found"];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
 
if (!isset($GETDATA['order_id']) || empty($GETDATA['order_id'])) {
    /////////////  YOUR CODE HERE   ///////////////////////////
    $data = ['status' => 2050, 'message' => "Order ID not found"];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
 
if ($GETDATA['status_code'] == 20000) {
    /////////////  YOUR CODE HERE   ///////////////////////////
    $data = ['status' => 20000, 'message' => "Order Cancelled"];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
 
if ($GETDATA['status_code'] != 200) {
    /////////////  YOUR CODE HERE   ///////////////////////////
    $data = ['status' => 2050, 'message' => "Order not complete"];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}


//****************** ADVANCE SECURITY CHECK  ***********************//
ksort($GETDATA);
$args = http_build_query($GETDATA);
$signature = hash_hmac('sha256', $GETDATA, $payerurl_secret_key);
if (!hash_equals($signature, $auth[1])) {
    $data = ['status' => 2030, 'message' => "Signature not matched"];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
//********************** ADVANCE SECURITY CHECK  *******************//


 
$data = ['status' => 2040, 'message' => $GETDATA];

/////////////  YOUR CODE HERE   ///////////////////////////
//
//
//
//
//
//
// change your order status
// all the security check is done
//
//
//
//
//
///////////// YOUR CODE HERE ///////////////////////////


$filename = "payerurl.log";
$fh = fopen($filename, "a");
fwrite($fh, json_encode($data));
fclose($fh); 
 
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit();
 
?>

