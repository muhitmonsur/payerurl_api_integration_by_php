# payerurl_api_integration_by_php
# payerurl_payment_request.php
The payerurl_payment_request.php is required to be setting up and making a request to the Payerurl API for processing a payment. It's designed to generate a unique order ID, specify order details, create a digital signature for authentication, send the payment request to the Payerurl API, and then redirect the user to the Payerurl payment page if the request is successful.

Here's a breakdown of what the code does:

1. It generates a unique order ID (`$invoiceid`) based on the current timestamp.

2. It sets the order total amount (`$amount`), currency (`$currency`), and billing user information.

3. Defines the redirect URL after a successful payment (`$redirect_to`), the notification URL for receiving payment responses (`$notify_url`), and the cancel URL in case the user cancels the payment (`$cancel_url`).

4. Specifies the Payerurl API credentials, including the public key (`$payerurl_public_key`) and secret key (`$payerurl_secret_key`). These keys are used for authentication with the Payerurl API.

5. Defines the order items in the `$items` array, including the item name, quantity, and price.

6. Constructs the API request parameters by sorting them, building an HTTP query string, and generating a digital signature using HMAC-SHA256.

7. Sends a POST request to the Payerurl API with the constructed parameters and the authorization header.

8. Processes the API response, which includes a redirect URL to the Payerurl payment page.

9. If the response is successful (HTTP status code 200) and contains a valid redirect URL, it redirects the user to the Payerurl payment page.

Please note you must have set up the Payerurl API correctly and have obtained the API credentials and endpoint URLs as mentioned in the comments. Also, make sure you handle any potential error scenarios and exceptions that may occur during the API request and response handling.
