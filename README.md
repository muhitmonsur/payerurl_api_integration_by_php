# Crypto currency payment gateway integration by PHP, customer payments receive in merchant wallet account,no middleware.  

The payerurl_payment_request.php is required to be setting up and making a request to the Payerurl API for processing a payment. It's designed to generate a unique order ID, specify order details, create a digital signature for authentication, send the payment request to the Payerurl API, and then redirect the user to the Payerurl payment page if the request is successful. Here's a breakdown of what the code does:
 1 It generates a unique order ID ($invoiceid) based on the current timestamp.
 2 It sets the order total amount ($amount), currency ($currency), and billing user information.
 3 Defines the redirect URL after a successful payment ($redirect_to), the notification URL for receiving payment responses ($notify_url), and the cancel URL in case the user cancels the payment ($cancel_url).
 4 Specifies the Payerurl API credentials, including the public key ($payerurl_public_key) and secret key ($payerurl_secret_key). These keys are used for authentication with the Payerurl API.
 5 Defines the order items in the $items array, including the item name, quantity, and price.
 6 Constructs the API request parameters by sorting them, building an HTTP query string, and generating a digital signature using HMAC-SHA256.
 7 Sends a POST request to the Payerurl API with the constructed parameters and the authorization header.
 8 Processes the API response, which includes a redirect URL to the Payerurl payment page.
 9 If the response is successful (HTTP status code 200) and contains a valid redirect URL, it redirects the user to the Payerurl payment page. Please note you must have set up the Payerurl API correctly and have obtained the API credentials and endpoint URLs as mentioned in the comments. Also, make sure you handle any potential error scenarios and exceptions that may occur during the API request and response handling.
payerurl_payment_response.php
The payerurl_payment_response.php is a callback or response script that handles notifications and responses from the Payerurl payment gateway. Here's a summary of what this script does:
 1 It begins by defining Payerurl API credentials, including the public and secret keys, for authentication.
 2 The script checks for the presence of an "Authorization" header in the HTTP request. If the header is not present, it attempts to extract authorization information from the POST data.
 3 It verifies the public key from the authorization header (or POST data) and compares it with the stored public key. If they do not match, an error response is sent.
 4 The script then collects various data from the POST request, including order ID, external transaction ID, transaction ID, status code, notes, and various other transaction details.
 5 It checks if a transaction ID is present and not empty. If the transaction ID is missing, it sends an error response.
 6 It also checks if an order ID is present and not empty. If the order ID is missing, it sends an error response.
 7 Depending on the status code received in the response, the script takes different actions. If the status code indicates a canceled order, it sends a response indicating the order is canceled. If the status code is not 200, it sends a response indicating the order is not complete.
 8 After all security checks and processing, the script constructs a response with a status code and a message, which includes the transaction data. It also logs this data to a file named "payerurl.log."
 9 Finally, the script sends a JSON response with the status code and message back to the calling system. In summary, this PHP script is designed to handle callback notifications from the Payerurl payment gateway. It performs various security checks and processes the transaction data, logging it and sending a JSON response back to the calling system based on the transaction's status code.
The 'payerurl_payment_success.php' to be called upon a successful payment.
The 'payerurl_payment_cancel.php' to be called when a payment is canceled by the user.
summary:
 1 payerurl_payment_request.php:
 ◦ Generates a unique order ID for a payment request.


