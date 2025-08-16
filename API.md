# toyyibPay Omnipay API Reference

Complete API reference for the toyyibPay Omnipay driver.

## Table of Contents

- [Gateway Class](#gateway-class)
- [Request Classes](#request-classes)
- [Response Classes](#response-classes)
- [Parameter Reference](#parameter-reference)
- [Error Codes](#error-codes)
- [Examples](#examples)

## Gateway Class

### `Omnipay\ToyyibPay\Gateway`

The main gateway class for toyyibPay integration.

#### Methods

##### `getName(): string`

Returns the gateway name.

```php
$gateway = Omnipay::create('ToyyibPay');
echo $gateway->getName(); // "toyyibPay"
```

##### `getDefaultParameters(): array`

Returns default gateway parameters.

```php
$defaults = $gateway->getDefaultParameters();
// Returns: ['testMode' => false]
```

##### `purchase(array $parameters = []): PurchaseRequest`

Creates a purchase request to generate a payment bill.

**Parameters:**
- `$parameters` (array): Payment parameters (see [Parameter Reference](#parameter-reference))

**Returns:** `PurchaseRequest` instance

**Example:**
```php
$request = $gateway->purchase([
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Product Purchase',
    'billDescription' => 'Payment for premium product',
    'billAmount' => 99.99,
    'billReturnUrl' => 'https://yoursite.com/return',
    'billCallbackUrl' => 'https://yoursite.com/callback',
    'billExternalReferenceNo' => 'ORDER-123',
    'billTo' => 'John Doe',
    'billEmail' => 'john@example.com',
    'billPhone' => '0123456789'
]);

$response = $request->send();
```

##### `completePurchase(array $parameters = []): CompletePurchaseRequest`

Creates a complete purchase request to verify payment status.

**Parameters:**
- `$parameters` (array): Verification parameters

**Returns:** `CompletePurchaseRequest` instance

**Example:**
```php
$request = $gateway->completePurchase([
    'billCode' => 'bill-code-from-callback'
]);

$response = $request->send();
```

## Request Classes

### `Omnipay\ToyyibPay\Message\AbstractRequest`

Base class for all toyyibPay requests.

#### Properties

- `$productionEndpoint` (string): `https://toyyibpay.com/`
- `$sandboxEndpoint` (string): `https://dev.toyyibpay.com/`

#### Methods

##### Parameter Getters and Setters

###### `getUserSecretKey(): string|null`
###### `setUserSecretKey(string $value): self`

Get/set the toyyibPay secret key.

```php
$request->setUserSecretKey('your-secret-key');
$secretKey = $request->getUserSecretKey();
```

###### `getCategoryCode(): string|null`
###### `setCategoryCode(string $value): self`

Get/set the category code.

```php
$request->setCategoryCode('your-category-code');
$categoryCode = $request->getCategoryCode();
```

###### `getBillName(): string|null`
###### `setBillName(string $value): self`

Get/set the bill name/title.

```php
$request->setBillName('Product Purchase');
$billName = $request->getBillName();
```

###### `getBillDescription(): string|null`
###### `setBillDescription(string $value): self`

Get/set the bill description.

```php
$request->setBillDescription('Payment for premium product');
$description = $request->getBillDescription();
```

###### `getBillAmount(): float`
###### `setBillAmount(float $value): self`

Get/set the bill amount. **Note:** The getter automatically converts to cents (multiplies by 100).

```php
$request->setBillAmount(99.99);
$amount = $request->getBillAmount(); // Returns 9999 (in cents)
```

###### `getBillReturnUrl(): string|null`
###### `setBillReturnUrl(string $value): self`

Get/set the return URL where customers are redirected after payment.

```php
$request->setBillReturnUrl('https://yoursite.com/payment/return');
$returnUrl = $request->getBillReturnUrl();
```

###### `getBillCallbackUrl(): string|null`
###### `setBillCallbackUrl(string $value): self`

Get/set the callback URL for payment notifications.

```php
$request->setBillCallbackUrl('https://yoursite.com/payment/callback');
$callbackUrl = $request->getBillCallbackUrl();
```

###### `getBillExternalReferenceNo(): string|null`
###### `setBillExternalReferenceNo(string $value): self`

Get/set your internal reference number.

```php
$request->setBillExternalReferenceNo('ORDER-123');
$refNo = $request->getBillExternalReferenceNo();
```

###### `getBillTo(): string|null`
###### `setBillTo(string $value): self`

Get/set the customer name.

```php
$request->setBillTo('John Doe');
$customerName = $request->getBillTo();
```

###### `getBillEmail(): string|null`
###### `setBillEmail(string $value): self`

Get/set the customer email.

```php
$request->setBillEmail('john@example.com');
$email = $request->getBillEmail();
```

###### `getBillPhone(): string|null`
###### `setBillPhone(string $value): self`

Get/set the customer phone number.

```php
$request->setBillPhone('0123456789');
$phone = $request->getBillPhone();
```

###### `getBillPriceSetting(): int|null`
###### `setBillPriceSetting(int $value): self`

Get/set price setting. `0` = fixed amount, `1` = open amount.

```php
$request->setBillPriceSetting(0); // Fixed amount
$priceSetting = $request->getBillPriceSetting();
```

###### `getBillPayorInfo(): int|null`
###### `setBillPayorInfo(int $value): self`

Get/set payor info requirement. `0` = optional, `1` = required.

```php
$request->setBillPayorInfo(1); // Required
$payorInfo = $request->getBillPayorInfo();
```

###### `getBillSplitPayment(): int|null`
###### `setBillSplitPayment(int $value): self`

Get/set split payment setting. `0` = disabled, `1` = enabled.

```php
$request->setBillSplitPayment(1); // Enabled
$splitPayment = $request->getBillSplitPayment();
```

###### `getBillSplitPaymentArgs(): string|null`
###### `setBillSplitPaymentArgs(string $value): self`

Get/set split payment arguments (JSON string).

```php
$splitArgs = json_encode([
    ['userSecretKey' => 'merchant1-key', 'amount' => 50.00],
    ['userSecretKey' => 'merchant2-key', 'amount' => 49.99]
]);
$request->setBillSplitPaymentArgs($splitArgs);
```

###### `getBillPaymentChannel(): int|null`
###### `setBillPaymentChannel(int $value): self`

Get/set payment channel restriction. `0` = all methods, `1` = FPX only, `2` = cards only.

```php
$request->setBillPaymentChannel(1); // FPX only
$paymentChannel = $request->getBillPaymentChannel();
```

###### `getBillDisplayMerchant(): int|null`
###### `setBillDisplayMerchant(int $value): self`

Get/set merchant display setting. `0` = hide, `1` = show.

```php
$request->setBillDisplayMerchant(1); // Show merchant info
$displayMerchant = $request->getBillDisplayMerchant();
```

###### `getBillContentEmail(): string|null`
###### `setBillContentEmail(string $value): self`

Get/set custom email content.

```php
$request->setBillContentEmail('Thank you for your purchase!');
$emailContent = $request->getBillContentEmail();
```

###### `getBillAdditionalField(): string|null`
###### `setBillAdditionalField(array $value): self`

Get/set additional custom fields. The setter automatically JSON encodes the array.

```php
$request->setBillAdditionalField([
    'order_id' => 123,
    'customer_tier' => 'premium',
    'notes' => 'Special handling required'
]);
$additionalFields = $request->getBillAdditionalField(); // Returns JSON string
```

###### `getBillChargeToCustomer(): int|null`
###### `setBillChargeToCustomer(int $value): self`

Get/set who pays the transaction fees. `1` = merchant pays, `2` = customer pays.

```php
$request->setBillChargeToCustomer(2); // Customer pays fees
$chargeToCustomer = $request->getBillChargeToCustomer();
```

###### `getBillCode(): string|null`
###### `setBillCode(string $value): self`

Get/set bill code (used in complete purchase requests).

```php
$request->setBillCode('bill-code-from-callback');
$billCode = $request->getBillCode();
```

###### `getBillpaymentStatus(): int|null`
###### `setBillpaymentStatus(int $value): self`

Get/set payment status filter for verification requests.

```php
$request->setBillpaymentStatus(1); // Filter for successful payments only
$paymentStatus = $request->getBillpaymentStatus();
```

##### Core Methods

###### `getHttpMethod(): string`

Returns the HTTP method for the request (always `POST`).

###### `getEndpoint(): string`

Returns the appropriate API endpoint based on test mode.

```php
$endpoint = $request->getEndpoint();
// Returns: https://dev.toyyibpay.com/ (test mode)
// Returns: https://toyyibpay.com/ (production mode)
```

###### `sendRequest(array $data): array`

Sends the HTTP request to toyyibPay API.

**Parameters:**
- `$data` (array): Request data including API endpoint

**Returns:** Decoded JSON response as array

### `Omnipay\ToyyibPay\Message\PurchaseRequest`

Handles bill creation requests.

#### Properties

- `$apiEndpoint` (string): `index.php/api/createBill`

#### Methods

##### `getData(): array`

Prepares and validates request data.

**Returns:** Array of validated request parameters

**Throws:** `InvalidRequestException` if required parameters are missing

##### `sendData(array $data): PurchaseResponse`

Sends the request and creates response object.

**Parameters:**
- `$data` (array): Request data

**Returns:** `PurchaseResponse` instance

### `Omnipay\ToyyibPay\Message\CompletePurchaseRequest`

Handles payment verification requests.

#### Properties

- `$apiEndpoint` (string): `index.php/api/getBillTransactions`

#### Methods

##### `getData(): array`

Prepares verification request data.

**Returns:** Array with bill code and optional payment status filter

##### `sendData(array $data): CompletePurchaseResponse`

Sends verification request and creates response object.

**Parameters:**
- `$data` (array): Request data

**Returns:** `CompletePurchaseResponse` instance

## Response Classes

### `Omnipay\ToyyibPay\Message\PurchaseResponse`

Handles bill creation responses.

#### Methods

##### `isSuccessful(): bool`

Always returns `false` as the initial response requires customer redirect.

```php
$response = $gateway->purchase($options)->send();
var_dump($response->isSuccessful()); // false
```

##### `isRedirect(): bool`

Returns `true` if bill was created successfully and customer should be redirected.

```php
if ($response->isRedirect()) {
    $response->redirect();
}
```

##### `getRedirectUrl(): string|null`

Returns the payment page URL for customer redirect.

```php
$paymentUrl = $response->getRedirectUrl();
// Returns: https://toyyibpay.com/{billCode}
```

##### `getRedirectData(): array`

Returns redirect data (always empty array for GET redirects).

```php
$redirectData = $response->getRedirectData(); // []
```

##### `getRedirectMethod(): string`

Returns redirect method (always `GET`).

```php
$method = $response->getRedirectMethod(); // "GET"
```

##### `getTransactionReference(): string|null`

Returns the bill code for tracking.

```php
$billCode = $response->getTransactionReference();
```

##### `getMessage(): string`

Returns error message if bill creation failed.

```php
if (!$response->isRedirect()) {
    echo $response->getMessage();
}
```

### `Omnipay\ToyyibPay\Message\CompletePurchaseResponse`

Handles payment verification responses.

#### Methods

##### `isSuccessful(): bool`

Returns `true` if payment was successful (status = 1).

```php
if ($response->isSuccessful()) {
    // Payment completed successfully
    $transactionId = $response->getTransactionId();
}
```

##### `isRedirect(): bool`

Returns `true` if payment is not successful and customer should be redirected back to payment page.

```php
if ($response->isRedirect()) {
    // Payment still pending or failed
    $response->redirect();
}
```

##### `getTransactionId(): string|null`

Returns the payment invoice number.

```php
$invoiceNo = $response->getTransactionId();
```

##### `getTransactionReference(): string|null`

Returns the bill permalink.

```php
$permalink = $response->getTransactionReference();
```

##### `getRedirectUrl(): string|null`

Returns the bill URL for redirect.

```php
$billUrl = $response->getRedirectUrl();
```

##### `getRedirectData(): array`

Returns redirect data (always empty array).

##### `getRedirectMethod(): string`

Returns redirect method (always `GET`).

##### `getMessage(): string`

Returns payment status message.

```php
$statusMessage = $response->getMessage();
// Possible values:
// "Successful transaction"
// "Pending transaction"
// "Unsuccessful transaction"
// "Pending"
// "Bill code is not valid. Please try again later or contact administrator for further assistance."
```

## Parameter Reference

### Required Parameters (Purchase)

| Parameter | Type | Description | Example |
|-----------|------|-------------|----------|
| `userSecretKey` | string | Your toyyibPay secret key | `"sk_live_1234567890"` |
| `categoryCode` | string | Category code from your account | `"CAT123"` |
| `billName` | string | Name/title of the bill | `"Premium Software License"` |
| `billDescription` | string | Description of the payment | `"1-year premium license with support"` |
| `billAmount` | float | Amount in RM | `99.99` |
| `billReturnUrl` | string | Return URL after payment | `"https://yoursite.com/return"` |
| `billCallbackUrl` | string | Callback URL for notifications | `"https://yoursite.com/callback"` |
| `billExternalReferenceNo` | string | Your internal reference | `"ORDER-123"` |
| `billTo` | string | Customer name | `"John Doe"` |
| `billEmail` | string | Customer email | `"john@example.com"` |
| `billPhone` | string | Customer phone | `"0123456789"` |

### Optional Parameters (Purchase)

| Parameter | Type | Default | Description | Values |
|-----------|------|---------|-------------|--------|
| `billPriceSetting` | int | `0` | Price setting | `0` = fixed, `1` = open amount |
| `billPayorInfo` | int | `1` | Customer info requirement | `0` = optional, `1` = required |
| `billSplitPayment` | int | `0` | Split payment setting | `0` = disabled, `1` = enabled |
| `billSplitPaymentArgs` | string | `""` | Split payment configuration | JSON string |
| `billPaymentChannel` | int | `0` | Payment method restriction | `0` = all, `1` = FPX only, `2` = cards only |
| `billDisplayMerchant` | int | `1` | Show merchant info | `0` = hide, `1` = show |
| `billContentEmail` | string | `""` | Custom email content | Any string |
| `billChargeToCustomer` | int | `2` | Who pays fees | `1` = merchant, `2` = customer |
| `billAdditionalField` | array | `[]` | Custom fields | Any key-value pairs |

### Required Parameters (Complete Purchase)

| Parameter | Type | Description | Example |
|-----------|------|-------------|----------|
| `billCode` | string | Bill code from purchase response | `"abc123def456"` |

### Optional Parameters (Complete Purchase)

| Parameter | Type | Description | Values |
|-----------|------|-------------|--------|
| `billpaymentStatus` | int | Filter by payment status | `1`, `2`, `3`, `4` |

## Error Codes

### Payment Status Codes

| Code | Status | Description |
|------|--------|-------------|
| `1` | Successful | Transaction completed successfully |
| `2` | Pending | Transaction is pending |
| `3` | Unsuccessful | Transaction failed |
| `4` | Pending | Alternative pending status |

### Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Invalid Secret Key" | Wrong or inactive secret key | Verify secret key and environment |
| "Category not found" | Invalid category code | Check category code in dashboard |
| "Invalid amount" | Amount is zero or negative | Ensure amount is positive |
| "Invalid email" | Malformed email address | Validate email format |
| "Invalid URL" | Malformed return/callback URL | Ensure URLs are valid and accessible |
| "Bill code is not valid" | Invalid or expired bill code | Check bill code and try again |

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| `200` | OK | Request successful |
| `400` | Bad Request | Invalid request parameters |
| `401` | Unauthorized | Invalid credentials |
| `404` | Not Found | Resource not found |
| `422` | Unprocessable Entity | Validation failed |
| `500` | Internal Server Error | Server error |

## Examples

### Basic Payment Flow

```php
use Omnipay\Omnipay;

// Initialize gateway
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true);

// Create payment
$response = $gateway->purchase([
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Product Purchase',
    'billDescription' => 'Premium product payment',
    'billAmount' => 99.99,
    'billReturnUrl' => 'https://yoursite.com/return',
    'billCallbackUrl' => 'https://yoursite.com/callback',
    'billExternalReferenceNo' => 'ORDER-' . time(),
    'billTo' => 'John Doe',
    'billEmail' => 'john@example.com',
    'billPhone' => '0123456789'
])->send();

if ($response->isRedirect()) {
    // Store bill code for later reference
    $billCode = $response->getTransactionReference();
    
    // Redirect customer to payment page
    header('Location: ' . $response->getRedirectUrl());
    exit;
} else {
    // Handle error
    echo 'Error: ' . $response->getMessage();
}
```

### Payment Verification

```php
// Verify payment (in return handler)
$billCode = $_GET['billcode'];

$response = $gateway->completePurchase([
    'billCode' => $billCode
])->send();

if ($response->isSuccessful()) {
    // Payment successful
    echo 'Payment successful!';
    echo 'Transaction ID: ' . $response->getTransactionId();
    echo 'Reference: ' . $response->getTransactionReference();
} elseif ($response->isRedirect()) {
    // Payment pending
    echo 'Payment pending, redirecting...';
    header('Location: ' . $response->getRedirectUrl());
    exit;
} else {
    // Payment failed
    echo 'Payment failed: ' . $response->getMessage();
}
```

### Split Payment

```php
// Configure split payment
$splitPaymentArgs = [
    [
        'userSecretKey' => 'merchant1-secret-key',
        'amount' => 50.00
    ],
    [
        'userSecretKey' => 'merchant2-secret-key',
        'amount' => 49.99
    ]
];

$response = $gateway->purchase([
    'userSecretKey' => 'main-merchant-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Marketplace Order',
    'billDescription' => 'Split payment order',
    'billAmount' => 99.99,
    'billSplitPayment' => 1,
    'billSplitPaymentArgs' => json_encode($splitPaymentArgs),
    // ... other required parameters
])->send();
```

### Custom Fields

```php
// Add custom fields
$customFields = [
    'order_id' => 12345,
    'customer_tier' => 'premium',
    'promotion_code' => 'SAVE20',
    'shipping_method' => 'express',
    'notes' => 'Handle with care'
];

$response = $gateway->purchase([
    'userSecretKey' => 'your-secret-key',
    'categoryCode' => 'your-category-code',
    'billName' => 'Custom Order',
    'billDescription' => 'Order with custom fields',
    'billAmount' => 149.99,
    'billAdditionalField' => $customFields,
    // ... other required parameters
])->send();
```

### Payment Channel Restriction

```php
// FPX (Online Banking) only
$response = $gateway->purchase([
    'billPaymentChannel' => 1, // FPX only
    // ... other parameters
]);

// Credit/Debit Cards only
$response = $gateway->purchase([
    'billPaymentChannel' => 2, // Cards only
    // ... other parameters
]);

// All payment methods (default)
$response = $gateway->purchase([
    'billPaymentChannel' => 0, // All methods
    // ... other parameters
]);
```

### Error Handling

```php
use Omnipay\Common\Exception\InvalidRequestException;

try {
    $response = $gateway->purchase($parameters)->send();
    
    if ($response->isRedirect()) {
        // Success
        header('Location: ' . $response->getRedirectUrl());
        exit;
    } else {
        // API error
        throw new Exception('Payment creation failed: ' . $response->getMessage());
    }
    
} catch (InvalidRequestException $e) {
    // Validation error (missing/invalid parameters)
    echo 'Validation error: ' . $e->getMessage();
    
} catch (Exception $e) {
    // General error
    echo 'Error: ' . $e->getMessage();
}
```

### Webhook Handling

```php
// webhook.php - Handle payment notifications
$billCode = $_POST['billcode'] ?? null;

if (!$billCode) {
    http_response_code(400);
    exit('Invalid callback');
}

try {
    $response = $gateway->completePurchase([
        'billCode' => $billCode
    ])->send();
    
    if ($response->isSuccessful()) {
        // Update order status in database
        updateOrderStatus($billCode, 'paid', [
            'transaction_id' => $response->getTransactionId(),
            'transaction_ref' => $response->getTransactionReference()
        ]);
        
        // Send confirmation email
        sendPaymentConfirmation($billCode);
    }
    
    // Always respond with 200 OK
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}
```

This API reference provides complete documentation for all classes, methods, parameters, and usage patterns in the toyyibPay Omnipay driver.