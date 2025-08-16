# Omnipay: toyyibPay

**toyyibPay driver for the Omnipay PHP payment processing library**

[![Latest Stable Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/sitehandy/omnipay-toyyibpay/releases/tag/v1.0.0)
[![PHP Version](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements toyyibPay support for Omnipay.

## Requirements

- PHP 7.4 or higher
- PHP 8.0, 8.1, 8.2, 8.3, 8.4 supported
- cURL extension
- JSON extension
- Omnipay 3.x

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `sitehandy/omnipay-toyyibpay` with Composer:

```bash
composer require league/omnipay sitehandy/omnipay-toyyibpay
```

> **Note**: Composer will automatically select the latest stable version. If you need a specific version, you can specify it, but using version constraints like `^1.0` may cause warnings.

### Version Information

- **v1.0.0**: First stable release with PHP 7.4-8.4 support, modern HTTP client, and comprehensive testing
- **Requirements**: PHP 7.4+ with cURL and JSON extensions
- **Compatibility**: Omnipay 3.x framework

## Table of Contents

- [Basic Usage](#basic-usage)
- [Quick Start](#quick-start)
- [Advanced Configuration](#advanced-configuration)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Security Best Practices](#security-best-practices)
- [Troubleshooting](#troubleshooting)
- [API Reference](#api-reference)

## Basic Usage

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## toyyibPay Category & Bill

Each payment is a `Bill`, which is under a `Category`. To begin, you need to open an account at [toyyibPay.com](https://toyyibpay.com/e/2238297686400) and then create a Category. Then, retrieve the `Category Code` for the gateway setup.

## Quick Start

```php
use Omnipay\Omnipay;

// Initialize the gateway
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true); // Use false for production

// Create a simple payment
$response = $gateway->purchase([
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Product Purchase',
    'billDescription' => 'Payment for product XYZ',
    'billAmount' => 50.00, // RM 50.00
    'billReturnUrl' => 'https://yoursite.com/return',
    'billCallbackUrl' => 'https://yoursite.com/callback',
    'billExternalReferenceNo' => 'ORDER-' . time(),
    'billTo' => 'John Doe',
    'billEmail' => 'john@example.com',
    'billPhone' => '0123456789'
])->send();

if ($response->isRedirect()) {
    $response->redirect();
} else {
    echo 'Error: ' . $response->getMessage();
}
```

## Advanced Configuration

### Complete Payment with All Options

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true);

$response = $gateway->purchase([
    // Required parameters
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Premium Product Package',
    'billDescription' => 'Complete package with premium features',
    'billAmount' => 299.99,
    'billReturnUrl' => 'https://yoursite.com/payment/return',
    'billCallbackUrl' => 'https://yoursite.com/payment/callback',
    'billExternalReferenceNo' => 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999),
    'billTo' => 'Jane Smith',
    'billEmail' => 'jane.smith@example.com',
    'billPhone' => '+60123456789',
    
    // Optional parameters
    'billPriceSetting' => 0, // 0=fixed, 1=open amount
    'billPayorInfo' => 1, // 0=optional, 1=required
    'billSplitPayment' => 0, // 0=disabled, 1=enabled
    'billSplitPaymentArgs' => '', // JSON string for split payment
    'billPaymentChannel' => 0, // 0=all, 1=FPX only, 2=credit card only
    'billDisplayMerchant' => 1, // 0=hide, 1=show merchant info
    'billContentEmail' => 'Thank you for your purchase! Your order will be processed within 24 hours.',
    'billChargeToCustomer' => 2, // 1=merchant, 2=customer pays fees
    
    // Custom fields (JSON encoded)
    'billAdditionalField' => [
        'product_id' => 'PROD-12345',
        'customer_tier' => 'premium',
        'promotion_code' => 'SAVE20',
        'notes' => 'Express delivery requested'
    ]
])->send();

if ($response->isRedirect()) {
    // Store transaction details before redirect
    $_SESSION['transaction_id'] = $response->getTransactionReference();
    $_SESSION['order_id'] = $options['billExternalReferenceNo'];
    
    $response->redirect();
} else {
    throw new Exception('Payment creation failed: ' . $response->getMessage());
}
```

### Split Payment Configuration

```php
// Example for split payment between multiple merchants
$splitPaymentArgs = [
    [
        'userSecretKey' => 'merchant1-secret-key',
        'amount' => 150.00
    ],
    [
        'userSecretKey' => 'merchant2-secret-key', 
        'amount' => 149.99
    ]
];

$response = $gateway->purchase([
    'userSecretKey' => 'main-merchant-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Marketplace Order',
    'billDescription' => 'Order from multiple vendors',
    'billAmount' => 299.99,
    'billSplitPayment' => 1,
    'billSplitPaymentArgs' => json_encode($splitPaymentArgs),
    // ... other required parameters
])->send();
```

### Custom Payment Channels

```php
// FPX (Online Banking) only
$response = $gateway->purchase([
    'billPaymentChannel' => 1, // FPX only
    // ... other parameters
]);

// Credit/Debit Card only
$response = $gateway->purchase([
    'billPaymentChannel' => 2, // Card only
    // ... other parameters
]);

// All payment methods (default)
$response = $gateway->purchase([
    'billPaymentChannel' => 0, // All methods
    // ... other parameters
]);
```

## Example

### How to create a Bill

Reference: https://toyyibpay.com/apireference/#cb

Bill serves as an invoice to your customer. The example below explains how you can create a toyyibPay Bill and then redirect the customer to the payment page.

```php
$gateway = Omnipay::create('ToyyibPay');

// Use sandbox mode for testing
$gateway->setTestMode(1); 

// You need to pass the following parameters to generate bill code
$options = [
    'userSecretKey' => 'YOURSECRETKEY',
    'categoryCode' => 'CATEGORYCODE',
    'billName' => 'Product Name',
    'billDescription' => 'Product Description',
    'billPriceSetting'=> 0,
    'billPayorInfo'=> 1,
    'billAmount'=> 1,
    'billReturnUrl'=>'https://yourwebsite.com/returnurl',
    'billCallbackUrl'=>'https://yourwebsite.com/callbackurl',
    'billExternalReferenceNo' => 'ORDER123',
    'billTo'=>'Customer Name',
    'billEmail'=>'customer@sampleemail.test',
    'billPhone'=>'0123456789',
    'billSplitPayment'=> 0,
    'billSplitPaymentArgs'=>'',
    'billPaymentChannel'=> 0,
    'billDisplayMerchant'=> 1,
    'billContentEmail' => 'Sample email content',
    'billChargeToCustomer' => 2
];

// Send a purchase request to create a bill
$response = $gateway->purchase($options)->send();

// Available response method
// $response->isSuccessful(); // is the response successful?
// $response->isRedirect(); // is the response a redirect?
// $response->getTransactionReference(); // a reference generated by the payment gateway
// $response->getTransactionId(); // the reference set by the originating website if available.
// $response->getMessage(); // a message generated by the payment gateway

// Now redirect the customer to the toyyibPay bill payment page
if ($response->isRedirect())
{
    // Do whatever you want here for example saving the data to the database etc
    // and then redirect the customer to the offsite payment gateway
    $response->redirect();
}
else
{
    // Display error message
    exit($response->getMessage());
}
```

### How to get Bill Transactions

Reference: https://toyyibpay.com/apireference/#gbt

You may check bill payment status by submitting Bill Code and Bill Payment Status(Optional).

The code below gives an example how to retrieve the bill transactions from the payment gateway server:

```php
$gateway = Omnipay::create('ToyyibPay');

// Parameters required
$options = [
    'billCode' => 'samplebillcode'
];

// Send a complete purchase request to get the bill transactions
$response = $gateway->completePurchase($options)->send();

// Available response method
// $response->isSuccessful(); // is the response successful?
// $response->isRedirect(); // is the response a redirect?
// $response->getTransactionReference(); // a reference generated by the payment gateway
// $response->getTransactionId(); // the reference set by the originating website if available.
// $response->getMessage(); // a message generated by the payment gateway

if ($response->isSuccessful())
{
    // Do whatever you want here for example saving the data to the database etc
    echo $responsive->getTransactionReference();
}
elseif ($response->isRedirect())
{
    // If the payment is not successful, redirect the customer to the payment page for completion
    $response->redirect();
}
else
{
    // Display error message
    exit($response->getMessage());
}
```

## Error Handling

### Comprehensive Error Handling

```php
use Omnipay\Omnipay;
use Omnipay\Common\Exception\InvalidRequestException;
use Exception;

try {
    $gateway = Omnipay::create('ToyyibPay');
    $gateway->setTestMode(true);
    
    $response = $gateway->purchase([
        'userSecretKey' => 'your-secret-key',
        'categoryCode' => 'your-category-code',
        'billName' => 'Test Payment',
        'billDescription' => 'Test payment description',
        'billAmount' => 10.00,
        'billReturnUrl' => 'https://yoursite.com/return',
        'billCallbackUrl' => 'https://yoursite.com/callback',
        'billExternalReferenceNo' => 'TEST-' . time(),
        'billTo' => 'Test Customer',
        'billEmail' => 'test@example.com',
        'billPhone' => '0123456789'
    ])->send();
    
    if ($response->isRedirect()) {
        // Success - redirect to payment page
        $response->redirect();
    } else {
        // Handle payment creation failure
        $errorMessage = $response->getMessage();
        error_log('toyyibPay payment creation failed: ' . $errorMessage);
        
        // Show user-friendly error message
        throw new Exception('Unable to process payment. Please try again later.');
    }
    
} catch (InvalidRequestException $e) {
    // Handle validation errors (missing required fields, etc.)
    error_log('toyyibPay validation error: ' . $e->getMessage());
    throw new Exception('Payment information is incomplete. Please check your details.');
    
} catch (Exception $e) {
    // Handle general errors
    error_log('toyyibPay general error: ' . $e->getMessage());
    throw new Exception('Payment service is temporarily unavailable. Please try again later.');
}
```

### Payment Completion Error Handling

```php
try {
    $response = $gateway->completePurchase([
        'billCode' => $_GET['billcode'] ?? null
    ])->send();
    
    if ($response->isSuccessful()) {
        // Payment successful
        $transactionId = $response->getTransactionId();
        $transactionRef = $response->getTransactionReference();
        
        // Update your database
        updateOrderStatus($transactionId, 'paid');
        
        // Redirect to success page
        header('Location: /payment/success?ref=' . $transactionRef);
        exit;
        
    } elseif ($response->isRedirect()) {
        // Payment still pending - redirect back to payment page
        $response->redirect();
        
    } else {
        // Payment failed
        $errorMessage = $response->getMessage();
        error_log('Payment verification failed: ' . $errorMessage);
        
        // Redirect to failure page
        header('Location: /payment/failed?error=' . urlencode($errorMessage));
        exit;
    }
    
} catch (Exception $e) {
    error_log('Payment completion error: ' . $e->getMessage());
    header('Location: /payment/error');
    exit;
}
```

## Testing

### Unit Testing Example

```php
use PHPUnit\Framework\TestCase;
use Omnipay\Omnipay;

class ToyyibPayGatewayTest extends TestCase
{
    private $gateway;
    
    protected function setUp(): void
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(true);
    }
    
    public function testPurchaseSuccess()
    {
        $options = [
            'userSecretKey' => 'test-secret-key',
            'categoryCode' => 'test-category',
            'billName' => 'Test Product',
            'billDescription' => 'Test Description',
            'billAmount' => 50.00,
            'billReturnUrl' => 'https://example.com/return',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'TEST-123',
            'billTo' => 'John Doe',
            'billEmail' => 'john@example.com',
            'billPhone' => '0123456789'
        ];
        
        $response = $this->gateway->purchase($options)->send();
        
        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
        $this->assertNotEmpty($response->getRedirectUrl());
    }
    
    public function testPurchaseWithMissingParameters()
    {
        $this->expectException(InvalidRequestException::class);
        
        $response = $this->gateway->purchase([
            'billName' => 'Test Product'
            // Missing required parameters
        ])->send();
    }
    
    public function testCompletePurchase()
    {
        $response = $this->gateway->completePurchase([
            'billCode' => 'test-bill-code'
        ])->send();
        
        // Response should be either successful, redirect, or have error message
        $this->assertTrue(
            $response->isSuccessful() || 
            $response->isRedirect() || 
            !empty($response->getMessage())
        );
    }
}
```

### Integration Testing with Mock Data

```php
// Test with sandbox environment
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true); // Always use test mode for testing

// Use test credentials (get these from toyyibPay sandbox)
$testOptions = [
    'userSecretKey' => 'sandbox-secret-key',
    'categoryCode' => 'sandbox-category-code',
    'billName' => 'Test Payment - ' . date('Y-m-d H:i:s'),
    'billDescription' => 'Integration test payment',
    'billAmount' => 1.00, // Use small amount for testing
    'billReturnUrl' => 'https://httpbin.org/get?return=true',
    'billCallbackUrl' => 'https://httpbin.org/post',
    'billExternalReferenceNo' => 'TEST-' . uniqid(),
    'billTo' => 'Test Customer',
    'billEmail' => 'test@example.com',
    'billPhone' => '0123456789'
];

$response = $gateway->purchase($testOptions)->send();

if ($response->isRedirect()) {
    echo "Test payment created successfully!\n";
    echo "Payment URL: " . $response->getRedirectUrl() . "\n";
    echo "Bill Code: " . $response->getTransactionReference() . "\n";
} else {
    echo "Test failed: " . $response->getMessage() . "\n";
}
```

## Security Best Practices

### 1. Secure Credential Management

```php
// Use environment variables for sensitive data
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');

$options = [
    'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'), // Never hardcode
    'categoryCode' => getenv('TOYYIBPAY_CATEGORY_CODE'),
    // ... other options
];
```

### 2. Validate Callback Data

```php
// In your callback handler
function handleCallback()
{
    // Verify the callback is from toyyibPay
    $billCode = $_POST['billcode'] ?? null;
    $status = $_POST['status_id'] ?? null;
    
    if (!$billCode || !$status) {
        http_response_code(400);
        exit('Invalid callback data');
    }
    
    // Verify payment status with toyyibPay API
    $gateway = Omnipay::create('ToyyibPay');
    $response = $gateway->completePurchase(['billCode' => $billCode])->send();
    
    if ($response->isSuccessful()) {
        // Update order status in your database
        updateOrderStatus($billCode, 'paid');
        
        // Send confirmation email
        sendPaymentConfirmation($billCode);
    }
    
    // Always respond with 200 OK to acknowledge receipt
    http_response_code(200);
    echo 'OK';
}
```

### 3. Input Validation and Sanitization

```php
function createPayment($orderData)
{
    // Validate and sanitize input data
    $billAmount = filter_var($orderData['amount'], FILTER_VALIDATE_FLOAT);
    if ($billAmount === false || $billAmount <= 0) {
        throw new InvalidArgumentException('Invalid amount');
    }
    
    $billEmail = filter_var($orderData['email'], FILTER_VALIDATE_EMAIL);
    if ($billEmail === false) {
        throw new InvalidArgumentException('Invalid email address');
    }
    
    $billPhone = preg_replace('/[^0-9+]/', '', $orderData['phone']);
    if (strlen($billPhone) < 10) {
        throw new InvalidArgumentException('Invalid phone number');
    }
    
    // Sanitize text fields
    $billName = htmlspecialchars(trim($orderData['product_name']), ENT_QUOTES, 'UTF-8');
    $billTo = htmlspecialchars(trim($orderData['customer_name']), ENT_QUOTES, 'UTF-8');
    
    return [
        'billAmount' => $billAmount,
        'billEmail' => $billEmail,
        'billPhone' => $billPhone,
        'billName' => $billName,
        'billTo' => $billTo
    ];
}
```

## Troubleshooting

### Common Issues and Solutions

#### 1. "Invalid Secret Key" Error

**Problem**: Getting authentication errors when creating bills.

**Solutions**:
- Verify your secret key is correct and active
- Check if you're using the right environment (sandbox vs production)
- Ensure the secret key matches the category code

```php
// Debug: Check your credentials
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true); // Make sure this matches your secret key environment

// Test with minimal required parameters first
$response = $gateway->purchase([
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Debug Test',
    'billDescription' => 'Testing credentials',
    'billAmount' => 1.00,
    'billReturnUrl' => 'https://example.com/return',
    'billCallbackUrl' => 'https://example.com/callback',
    'billExternalReferenceNo' => 'DEBUG-' . time(),
    'billTo' => 'Test',
    'billEmail' => 'test@example.com',
    'billPhone' => '0123456789'
])->send();
```

#### 2. Callback Not Working

**Problem**: Not receiving payment notifications.

**Solutions**:
- Ensure callback URL is publicly accessible (not localhost)
- Check server logs for incoming requests
- Verify callback URL returns HTTP 200 status
- Test callback URL manually

```php
// Debug callback handler
function debugCallback()
{
    // Log all incoming data
    error_log('Callback received: ' . print_r($_POST, true));
    error_log('Headers: ' . print_r(getallheaders(), true));
    
    // Always respond with 200
    http_response_code(200);
    echo 'OK';
}
```

#### 3. Amount Formatting Issues

**Problem**: Incorrect amount being charged.

**Solution**: toyyibPay expects amount in cents (multiply by 100).

```php
// Correct amount formatting
$amountInRM = 50.00; // RM 50.00
$response = $gateway->purchase([
    'billAmount' => $amountInRM, // Library handles conversion automatically
    // ... other parameters
]);

// The library automatically converts RM to cents internally
// RM 50.00 becomes 5000 cents
```

#### 4. SSL/TLS Issues

**Problem**: Connection errors when making API calls.

**Solutions**:
- Ensure your server supports TLS 1.2 or higher
- Update cURL and OpenSSL libraries
- Check firewall settings

```php
// Test SSL connectivity
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://toyyibpay.com/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "SSL Error: " . $error;
} else {
    echo "SSL connection successful";
}
```

### Debug Mode

```php
// Enable debug logging
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true);

// Log all requests and responses
class DebugHttpClient implements \Http\Client\HttpClient
{
    private $client;
    
    public function __construct($client)
    {
        $this->client = $client;
    }
    
    public function sendRequest($request)
    {
        error_log('Request: ' . $request->getMethod() . ' ' . $request->getUri());
        error_log('Body: ' . $request->getBody());
        
        $response = $this->client->sendRequest($request);
        
        error_log('Response: ' . $response->getStatusCode());
        error_log('Response Body: ' . $response->getBody());
        
        return $response;
    }
}

// Use debug client
$gateway->setHttpClient(new DebugHttpClient($gateway->getHttpClient()));
```

## API Reference

### Gateway Methods

#### `purchase(array $options)`
Creates a new bill for payment.

**Required Parameters:**
- `userSecretKey` (string): Your toyyibPay secret key
- `categoryCode` (string): Category code from your toyyibPay account
- `billName` (string): Name/title of the bill
- `billDescription` (string): Description of the payment
- `billAmount` (float): Amount in RM (e.g., 50.00 for RM 50)
- `billReturnUrl` (string): URL to redirect after payment
- `billCallbackUrl` (string): URL for payment notifications
- `billExternalReferenceNo` (string): Your internal reference number
- `billTo` (string): Customer name
- `billEmail` (string): Customer email
- `billPhone` (string): Customer phone number

**Optional Parameters:**
- `billPriceSetting` (int): 0=fixed amount, 1=open amount
- `billPayorInfo` (int): 0=optional customer info, 1=required
- `billSplitPayment` (int): 0=disabled, 1=enabled
- `billSplitPaymentArgs` (string): JSON string for split payment configuration
- `billPaymentChannel` (int): 0=all methods, 1=FPX only, 2=cards only
- `billDisplayMerchant` (int): 0=hide merchant info, 1=show
- `billContentEmail` (string): Custom email content
- `billChargeToCustomer` (int): 1=merchant pays fees, 2=customer pays fees
- `billAdditionalField` (array): Custom fields (will be JSON encoded)

#### `completePurchase(array $options)`
Retrieves bill transaction status.

**Required Parameters:**
- `billCode` (string): Bill code returned from purchase request

**Optional Parameters:**
- `billpaymentStatus` (int): Filter by payment status

### Response Methods

#### Purchase Response
- `isRedirect()`: Returns true if customer should be redirected
- `getRedirectUrl()`: Gets the payment page URL
- `getTransactionReference()`: Gets the bill code
- `getMessage()`: Gets error message if failed

#### Complete Purchase Response
- `isSuccessful()`: Returns true if payment was successful
- `isRedirect()`: Returns true if payment is still pending
- `getTransactionId()`: Gets the payment invoice number
- `getTransactionReference()`: Gets the bill permalink
- `getMessage()`: Gets status message

### Payment Status Codes

- `1`: Successful transaction
- `2`: Pending transaction  
- `3`: Unsuccessful transaction
- `4`: Pending (alternative status)

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/sitehandy/omnipay-toyyibpay/issues),
or better yet, fork the library and submit a pull request.