<?php
/**
 * unique order ID, this order number must be unique.
 */
$invoiceid = floor(microtime(true) * 1000);
 
/**
 * Order Total Amount
 */
$amount = 123;
 
/**
 * Order amount currency
 */
$currency = 'usd';
 
/**
 * Billing user info
 */
$billing_fname = 'First name';
$billing_lname = 'Last name';
$billing_email = 'test@email.com';
 
/**
 * After successful payment customer will redirect to this url.
 */
$redirect_to = 'http://localhost/pt/payerurl_payment_success.php';



 
/*****
**** THIS IS VERY IMPORTANT VARIABLE *******************
 * Response URL/cancel URL/ Callback URL/ our system will only send response to this url
 *****/
$notify_url = 'https://test.payerurl.com/payerurl_payment_response.php';
//Note: It is the web address for our server's payerurl_payment_response.php file.
 
/**
 * If you user cancel any payment, user will redirect to cancel url
 */
$cancel_url = 'http://localhost/pt/payerurl_payment_cancel.php';
 
/**
 * Payerurl API credentials
 * Do not share the credentials
 * Get your API key : https://dashboard.payerurl.com/profile/api-management 
 */

$payerurl_public_key = 'de1e85e8a087fed83e4a3ba9dfe36f08';  // this credencials open for public
$payerurl_secret_key = '0a634fc47368f55f1f54e472283b3acd'; // this credencials open for public
 
/**
 * Order items
 */
$items = [
    [
        'name' => 'Order item name',
        'qty' => 'Order item quantity',
        'price' => '123',
    ]
];
 
/**
 * API params
 */
$args = [
    'order_id' => $invoiceid,
    'amount' => $amount,
    'items' => $items,
    'currency' => $currency,
    'billing_fname' => $billing_fname,
    'billing_lname' => $billing_lname,
    'billing_email' => $billing_email,
    'redirect_to' => $redirect_to,
    'notify_url' => $notify_url,
    'cancel_url' => $cancel_url,
    'type' => 'php',
];
 
/**
 * Generate signature
 */
ksort($args);
$args = http_build_query($args);
$signature = hash_hmac('sha256', $args, $payerurl_secret_key);
$authStr = base64_encode(sprintf('%s:%s', $payerurl_public_key, $signature));
 
/**
 * Send API response
 */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://test.payerurl.com/api/payment');
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type:application/x-www-form-urlencoded;charset=UTF-8',
    'Authorization:' . sprintf('Bearer %s', $authStr),
]);
 
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
 
$response = json_decode($response);
 
/**
 * Redirect user to payerurl payment page
 */
if ($httpCode === 200 && isset($response->redirectTO) && !empty($response->redirectTO)) {
    header('Location: ' . $response->redirectTO);
}
exit();
?>
