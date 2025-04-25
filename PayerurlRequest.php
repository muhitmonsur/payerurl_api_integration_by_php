<?php

class PayerUrlRequest
{
    public function payment($invoiceId, $amount, $currency = 'usd', $data)
    {
        try {
            // Basic validation
            if (empty($invoiceId) || empty($amount) || !is_array($data)) {
                throw new InvalidArgumentException("Invalid input data provided.");
            }

            // Set billing details with defaults
            $billingFname = $data['first_name'] ?? 'First name';
            $billingLname = $data['last_name'] ?? 'Last name';
            $billingEmail = $data['email'] ?? 'test@email.com';

            // Redirect URLs
            $redirectUrl = $data['redirect_url'] ?? null;
            $notifyUrl   = $data['notify_url'] ?? null;
            $cancelUrl   = $data['cancel_url'] ?? null;

            if (!$redirectUrl || !$notifyUrl || !$cancelUrl) {
                throw new InvalidArgumentException("Missing redirect, notify, or cancel URL.");
            }

            // API credentials
            $publicKey = $data['public_key'] ?? null;
            $secretKey = $data['secret_key'] ?? null;

            if (!$publicKey || !$secretKey) {
                throw new InvalidArgumentException("Missing API credentials.");
            }

            // Order items (for example, this can be parameterized)
            $items = [
                [
                    'name'  => str_replace(' ', '_', 'Order item name'),
                    'qty'   => 1, // Make sure to replace with actual quantity
                    'price' => 123, // Replace with actual price
                ]
            ];

            // Prepare API parameters
            $params = [
                'order_id'      => $invoiceId,
                'amount'        => $amount,
                'items'         => $items,
                'currency'      => strtolower($currency),
                'billing_fname' => $billingFname,
                'billing_lname' => $billingLname,
                'billing_email' => $billingEmail,
                'redirect_to'   => $redirectUrl,
                'notify_url'    => $notifyUrl,
                'cancel_url'    => $cancelUrl,
                'type'          => 'php',
            ];

            // Generate signature
            ksort($params);
            $queryString = http_build_query($params);
            $signature = hash_hmac('sha256', $queryString, $secretKey);
            $authHeader = 'Bearer ' . base64_encode("$publicKey:$signature");

            // Execute API request
            $ch = curl_init('https://api-v2.payerurl.com/api/payment');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => $queryString,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                    'Authorization: ' . $authHeader,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response);

            if ($httpCode === 200 && isset($result->redirectTO) && !empty($result->redirectTO)) {
                header('Location: ' . $result->redirectTO);
                exit;
            }

            return [
                'status'  => false,
                'message' => $result->message ?? 'Something went wrong during payment initialization.',
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}