# PayerURL — Payment Request API

Send a payment request to PayerURL and redirect the customer to the checkout page.

> Get your API keys: https://dash.payerurl.com/profile/api-management

---

## Requirements

- PHP 7.4+
- Extensions: `curl`, `json`

---

## Quick Start

```php
// 1. Set your order details
$invoiceid = floor(microtime(true) * 1000); // unique order ID
$amount    = 120.00;
$currency  = 'usd';

// 2. Set customer info
$billing_fname = 'John';
$billing_lname = 'Doe';
$billing_email = 'john@example.com';

// 3. Set URLs
$redirect_to = 'https://yoursite.com/payment-success';
$notify_url  = 'https://yoursite.com/payment-notify';
$cancel_url  = 'https://yoursite.com/checkout';

// 4. Set API keys
$payerurl_public_key = 'your_public_key';
$payerurl_secret_key = 'your_secret_key';
```

---

## Request Parameters

| Parameter       | Type   | Required | Description                                          |
|-----------------|--------|----------|------------------------------------------------------|
| `order_id`      | int    | ✅       | Unique order ID. Must never repeat.                  |
| `amount`        | float  | ✅       | Total order amount (e.g. `120.00`)                   |
| `currency`      | string | ✅       | Lowercase currency code (e.g. `usd`, `bdt`)         |
| `items`         | array  | ✅       | List of order items (see below)                      |
| `billing_fname` | string | ✅       | Customer first name                                  |
| `billing_lname` | string | ✅       | Customer last name                                   |
| `billing_email` | string | ✅       | Customer email address                               |
| `redirect_to`   | string | ✅       | URL to redirect after successful payment             |
| `notify_url`    | string | ✅       | URL where PayerURL sends the payment callback        |
| `cancel_url`    | string | ✅       | URL to redirect if customer cancels                  |
| `type`          | string | ✅       | Always set to `php`                                  |

---

## Items Array

Each item in the `$items` array must follow this structure:

```php
$items = [
    [
        'name'  => 'Order_Item_Name', // No spaces — use underscore
        'qty'   => '2',               // String, integer value
        'price' => '60.00',           // String, float value
    ]
];
```

| Field   | Type   | Description                              |
|---------|--------|------------------------------------------|
| `name`  | string | Item name. **No spaces** — use `_`       |
| `qty`   | string | Quantity as string (e.g. `"2"`)          |
| `price` | string | Unit price as string (e.g. `"60.00"`)   |

---

## Authentication

Parameters are sorted, URL-encoded, then signed with HMAC-SHA256. The result is base64-encoded and sent as a Bearer token:

```php
ksort($args);
$args      = http_build_query($args);
$signature = hash_hmac('sha256', $args, $payerurl_secret_key);
$authStr   = base64_encode($payerurl_public_key . ':' . $signature);

// Sent in header as:
// Authorization: Bearer <authStr>
```

---

## API Endpoint

```
POST https://api-v2.payerurl.com/api/payment
Content-Type: application/x-www-form-urlencoded;charset=UTF-8
Authorization: Bearer <authStr>
```

---

## Response & Redirect

On success (HTTP 200), the API returns a `redirectTO` URL. Redirect the customer to it:

```php
if ($httpCode === 200 && !empty($response->redirectTO)) {
    header('Location: ' . $response->redirectTO);
    exit();
}
```

### Success Response

```json
{
  "redirectTO": "https://pay.payerurl.com/checkout/abc123"
}
```

### Failure Response

```json
{
  "status": false,
  "message": "Invalid signature."
}
```

---

## URL Roles

| URL           | When It's Called                                          |
|---------------|-----------------------------------------------------------|
| `redirect_to` | Customer is sent here after successful payment            |
| `notify_url`  | PayerURL POSTs payment details here (server-to-server)    |
| `cancel_url`  | Customer is sent here if they cancel the payment          |

> ⚠️ `notify_url` must be a **publicly accessible HTTPS URL**. Localhost will not work.

---

## Full Example

```php
<?php
$invoiceid           = floor(microtime(true) * 1000);
$amount              = 120.00;
$currency            = 'usd';
$billing_fname       = 'John';
$billing_lname       = 'Doe';
$billing_email       = 'john@example.com';
$redirect_to         = 'https://yoursite.com/payment-success';
$notify_url          = 'https://yoursite.com/payment-notify';
$cancel_url          = 'https://yoursite.com/checkout';
$payerurl_public_key = 'your_public_key';
$payerurl_secret_key = 'your_secret_key';

$items = [
    [
        'name'  => 'Product_Name',
        'qty'   => '10',
        'price' => '12.00',
    ]
];

$args = [
    'order_id'      => $invoiceid,
    'amount'        => $amount,
    'items'         => $items,
    'currency'      => strtolower(trim($currency)),
    'billing_fname' => $billing_fname,
    'billing_lname' => $billing_lname,
    'billing_email' => $billing_email,
    'redirect_to'   => $redirect_to,
    'notify_url'    => $notify_url,
    'cancel_url'    => $cancel_url,
    'type'          => 'php',
];

ksort($args);
$args      = http_build_query($args);
$signature = hash_hmac('sha256', $args, $payerurl_secret_key);
$authStr   = base64_encode($payerurl_public_key . ':' . $signature);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'https://api-v2.payerurl.com/api/payment',
    CURLOPT_POST           => true,
    CURLOPT_HEADER         => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $args,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Authorization: Bearer ' . $authStr,
    ],
]);

$response = json_decode(curl_exec($ch));
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($response->redirectTO)) {
    header('Location: ' . $response->redirectTO);
}
exit();
```

---

## Security Notes

- Never expose your `secret_key` in client-side code or public repositories
- Use environment variables for credentials in production:

```php
$payerurl_public_key = $_ENV['PAYERURL_PUBLIC_KEY'];
$payerurl_secret_key = $_ENV['PAYERURL_SECRET_KEY'];
```

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















