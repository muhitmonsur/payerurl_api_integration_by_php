## 💳 PayerURL Payment Integration – PHP
###### This method allows you to integrate with the PayerURL Payment Gateway using a simple PHP function. It's designed for systems where server-to-server communication is preferred over frontend SDKs.

## 📌 Function: payment($invoiceId, $amount, $currency = 'usd', $data)
###### Handles the payment process with PayerURL API and redirects the customer to the payment page.

## ✅ Required Parameters
| Name | Type | Required | Description |
| --- | --- | --- | --- |
| $invoiceId | string | ✅ | Unique invoice or order ID. |
| $amount | int | ✅ | Payment amount (in smallest currency unit, e.g., cents). |
| $currency | string | ❌ | Currency code (e.g., usd, bdt). Default: usd. |
| $data | array | ✅ | Contains customer info, redirect URLs, and API credentials. |


## 🔑 $data Array Structure

~~~php
$data = [
    'first_name'   => 'John',             // Optional
    'last_name'    => 'Doe',              // Optional
    'email'        => 'john@example.com', // Optional
    'redirect_url' => 'https://yourdomain.com/payment-success',
    'notify_url'   => 'https://yourdomain.com/api/payment-notify',
    'cancel_url'   => 'https://yourdomain.com/checkout',
    'public_key'   => 'your_public_key',
    'secret_key'   => 'your_secret_key',
];
~~~

## 🔑GET API KEY
Get your API key : https://dash.payerurl.com/profile/get-api-credentials
<img src="https://raw.githubusercontent.com/RashiqulRony/rony.mmj/refs/heads/master/payerurl.png">



## 🚀 How It Works
1. Collect user and order info on your platform.
2. Call the payment() function with required details.
3. User is redirected to PayerURL payment page.
4. After payment:
    * User is redirected to redirect_url.
    * Your backend receives a callback at notify_url with transaction details.
    * On cancellation, user is returned to cancel_url.


## 🔐 Authentication
Authentication is done via HMAC SHA256 signature using your secret key. The request is then base64-encoded and added as a Bearer token.


## 🧪 Sample Usage
###### Download `PayerUrlRequest.php` Class and using your any php project. Example: 

~~~php
require_once 'PayerUrlRequest.php';
$request = new PayerUrlRequest();

$invoiceId = 'INV-1001';
$amount = 1000; // $10.00
$currency = 'usd';

$data = [
    'first_name' => 'Alice',
    'last_name' => 'Smith',
    'email' => 'alice@example.com',
    'redirect_url' => 'https://yoursite.com/payment-success',
    'notify_url' => 'https://yoursite.com/api/payment-notify',
    'cancel_url' => 'https://yoursite.com/cart',
    'public_key' => 'pk_live_xxxxxx',
    'secret_key' => 'sk_live_xxxxxx',
];

$request->payment($invoiceId, $amount, $currency, $data);
~~~














