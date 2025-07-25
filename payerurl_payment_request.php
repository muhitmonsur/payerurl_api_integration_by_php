<?php
/**
 * unique order ID, this order number must be unique.
 */
$invoiceid = floor(microtime(true) * 1000);
 
/**
 * Order Total Amount
 */
$amount = 123;  //integer value
 
/**
 * Order amount currency
 */
$currency = 'usd';  // currency in small letter
 
/**
 * Billing user info
 */
$billing_fname = 'First name';
$billing_lname = 'Last name';
$billing_email = 'test@email.com';
 
/**
 * After successful payment customer will redirect to this url.
 */
$redirect_to = 'http://localhost/pt/payerurl_payment_success.php'; //(order receive page)



 
/*****
**** THIS IS VERY IMPORTANT VARIABLE *******************
 * Response URL/Callback URL our system will only send response to this url
 *****/


//Note: after payment complete our system automatically sent payment detail on this notify_url in few seconds.
$notify_url = 'https://test.payerurl.com/payerurl_payment_response.php';  //(system will sent payment info in this page)

 
/**
 * If you user cancel any payment, user will redirect to cancel url
 */
$cancel_url = 'http://localhost/pt/payerurl_payment_cancel.php'; //(checkout page)
 
/**
 * Payerurl API credentials
 * Do not share the credentials
 * Get your API key : https://test.payerurl.com/profile/api-management 
 */

$payerurl_public_key = 'your_payeurl_api_key';  // this credencials open for public
$payerurl_secret_key = 'your_payeurl_secret_key'; // this credencials open for public
 
/**
 * Order items
 */
$items = [
    [
        'name' => str_replace(' ', '_', 'Order item name'), // Replace spaces with '_' , no space allowed
        'qty' => 'Order item quantity',
        'price' => '123',
    ]
];
 
/**
 * API params
 */
$args = [
    'order_id' => $invoiceid,  // must be unique
    'amount' => $amount, //integer value
    'items' => $items, 
    'currency' => $currency,  // currency in small letter
    'billing_fname' => $billing_fname,
    'billing_lname' => $billing_lname,
    'billing_email' => $billing_email,
    'redirect_to' => $redirect_to,  //After successful payment customer will redirect to this url.(order receive page)
    'notify_url' => $notify_url,  // PayerURL will send a callback to this URL once the payment is successfully completed.
    'cancel_url' => $cancel_url,  //If you user cancel any payment, user will redirect to cancel url.(checkout page)
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
curl_setopt($ch, CURLOPT_URL, 'https://api-v2.payerurl.com/api/payment');
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
