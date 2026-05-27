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

$invoiceid = floor(microtime(true) * 1000);
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


# PayerURL — Withdraw Request API

A PHP integration for sending crypto withdrawal requests via the [PayerURL](https://payerurl.com) API.

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 7.4 or higher |
| Extensions | `curl`, `json` |

---

## Quick Start

```bash
# Clone or copy withdraw-request.php into your project
php withdraw-request.php
```

---

## Configuration

Open `withdraw-request.php` and update the constants at the top:

```php
const PAYERURL_PUBLIC_KEY = 'your_public_key_here';
const PAYERURL_SECRET_KEY = 'your_secret_key_here';
```

> Your keys are available in your PayerURL merchant dashboard.

---

## Request Parameters

| Parameter      | Type   | Required | Description                                      |
|----------------|--------|----------|--------------------------------------------------|
| `amount`       | number | ✅       | Withdrawal amount in fiat (e.g. `123`)           |
| `coin_address` | string | ✅       | Destination wallet address                       |
| `coin_Network` | string | ✅       | Coin network ID (see table below)                |
| `rcvr_id`      | string | ✅       | Customer ID from your own system                 |
| `rcvr_email`   | string | ✅       | Customer email from your own system              |

### Example

```php
$params = [
    'amount'       => 123,
    'coin_address' => 'TXxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'coin_Network' => '1',
    'rcvr_id'      => '4551',
    'rcvr_email'   => 'customer@example.com',
];
```

---

## Supported Coin Networks

| ID | Network            |
|----|--------------------|
| 1  | TRC20-USDT         |
| 40 | ERC20-USDT         |
| 43 | BEP20-USDT         |
| 35 | ERC20-USDC         |
| 44 | BEP20-USDC         |
| 9  | ERC20-Ethereum     |
| 12 | BTC-bitcoin        |


---

## How It Works

### 1. Signature Generation

Parameters are sorted alphabetically by key, URL-encoded, then signed with HMAC-SHA256 using your secret key:

```php
ksort($params);
$query     = http_build_query($params);
$signature = hash_hmac('sha256', $query, $secretKey);
$authStr   = base64_encode($publicKey . ':' . $signature);
```

### 2. Payload Submission

The signed `auth_str` is merged into the payload and sent as a `POST` request with `application/x-www-form-urlencoded` encoding to:

```
POST https://api-v2.payerurl.com/api/withdraw-request-public
```

---

## API Response

| Field         | Type    | Description                              |
|---------------|---------|------------------------------------------|
| `status`      | boolean | `true` = success, `false` = failure      |
| `return_code` | string  | Machine-readable result code             |
| `message`     | string  | Human-readable result message            |
| `rcvr_id`     | string  | Echo of the submitted customer ID        |
| `rcvr_email`  | string  | Echo of the submitted customer email     |

### Success Response Example

```json
{
  "status": true,
  "return_code": "2000",
  "message": "Withdraw request submitted successfully.",
  "rcvr_id": "4551",
  "rcvr_email": "customer@example.com"
}
```

### Failure Response Example

```json
{
  "status": false,
  "return_code": "4001",
  "message": "Invalid signature.",
  "rcvr_id": null,
  "rcvr_email": null
}
```

---

## Error Handling

| Scenario                  | Behavior                                           |
|---------------------------|----------------------------------------------------|
| cURL connection failure   | Returns `null`, logs error via `error_log()`       |
| Non-200 HTTP response     | Logs HTTP code, still returns decoded body         |
| Invalid JSON response     | Returns `null`                                     |
| Missing response fields   | Uses `?? 'N/A'` fallback when printing             |

---

## Security Notes

- **Never commit** your `PAYERURL_SECRET_KEY` to version control.
- Store keys in environment variables or a secrets manager in production:

```php
const PAYERURL_PUBLIC_KEY = $_ENV['PAYERURL_PUBLIC_KEY'];
const PAYERURL_SECRET_KEY = $_ENV['PAYERURL_SECRET_KEY'];
```

- Always validate `amount` and `coin_address` before calling the API.















