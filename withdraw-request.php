<?php

/**
 * PayerURL Withdraw Request
 * Sends a crypto withdrawal request to the PayerURL API.
 */

// ── Configuration ──────────────────────────────────────────────
const PAYERURL_API_URL   = 'https://api-v2.payerurl.com/api/withdraw-request-public';
const PAYERURL_PUBLIC_KEY = 'your payerurl api key';
const PAYERURL_SECRET_KEY = 'your payerurl secret key';

/**
 * Supported coin networks:
 *  1  => TRC20-USDT
 *  40 => ERC20-USDT
 *  43 => BEP20-USDT
 *  35 => ERC20-USDC
 *  44 => BEP20-USDC
 *  9  => ERC20-Ethereum
 *  12 => BTC-bitcoin
 */

// ── Request Parameters ─────────────────────────────────────────
$params = [
    'amount'       => 123,
    'coin_address' => 'your_provided_wallet_address',
    'coin_Network' => '1',                   // 1 = TRC20-USDT
    'rcvr_id'      => '4551',               // Customer ID in your system
    'rcvr_email'   => 'mirmonsor@gmail.com', // Customer email in your system
];

// ── Send Request ───────────────────────────────────────────────
$response = sendWithdrawRequest($params, PAYERURL_PUBLIC_KEY, PAYERURL_SECRET_KEY);

// ── Handle Response ────────────────────────────────────────────
if ($response === null) {
    die('Error: Failed to connect to PayerURL API.');
}

echo "Status:     " . ($response->status      ?? 'N/A') . "\n";
echo "Return Code:" . ($response->return_code  ?? 'N/A') . "\n";
echo "Message:    " . ($response->message      ?? 'N/A') . "\n";
echo "Receiver ID:" . ($response->rcvr_id      ?? 'N/A') . "\n";
echo "Email:      " . ($response->rcvr_email   ?? 'N/A') . "\n";

// ── Functions ──────────────────────────────────────────────────

/**
 * Build HMAC signature and auth string from params.
 *
 * @param array  $params     Request parameters (without auth).
 * @param string $publicKey  PayerURL public key.
 * @param string $secretKey  PayerURL secret key.
 * @return string            Base64-encoded auth string.
 */
function buildAuthString(array $params, string $publicKey, string $secretKey): string
{
    ksort($params);
    $query     = http_build_query($params);
    $signature = hash_hmac('sha256', $query, $secretKey);
    return base64_encode($publicKey . ':' . $signature);
}

/**
 * Send the withdrawal request to PayerURL API.
 *
 * @param array  $params     Request parameters.
 * @param string $publicKey  PayerURL public key.
 * @param string $secretKey  PayerURL secret key.
 * @return object|null       Decoded JSON response or null on failure.
 */
function sendWithdrawRequest(array $params, string $publicKey, string $secretKey): ?object
{
    $authStr = buildAuthString($params, $publicKey, $secretKey);

    $payload = http_build_query(
        array_merge($params, ['auth_str' => $authStr]),
        '',
        '&',
        PHP_QUERY_RFC3986
    );

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => PAYERURL_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || !empty($curlError)) {
        error_log('PayerURL cURL error: ' . $curlError);
        return null;
    }

    if ($httpCode !== 200) {
        error_log('PayerURL API returned HTTP ' . $httpCode);
    }

    return json_decode($raw) ?? null;
}
