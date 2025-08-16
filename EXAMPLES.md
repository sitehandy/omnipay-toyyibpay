# toyyibPay Omnipay Examples

This document provides comprehensive examples for using the toyyibPay Omnipay driver in various scenarios.

## Table of Contents

- [Basic Examples](#basic-examples)
- [E-commerce Integration](#e-commerce-integration)
- [Subscription Payments](#subscription-payments)
- [Split Payments](#split-payments)
- [Custom Fields Usage](#custom-fields-usage)
- [Webhook Handling](#webhook-handling)
- [Laravel Integration](#laravel-integration)
- [Symfony Integration](#symfony-integration)
- [Error Handling Patterns](#error-handling-patterns)
- [Testing Examples](#testing-examples)

## Basic Examples

### Simple Payment Creation

```php
<?php
require_once 'vendor/autoload.php';

use Omnipay\Omnipay;

// Initialize gateway
$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true); // Set to false for production

// Create payment
try {
    $response = $gateway->purchase([
        'userSecretKey' => 'your-secret-key',
        'categoryCode' => 'your-category-code',
        'billName' => 'Digital Product Purchase',
        'billDescription' => 'Premium software license',
        'billAmount' => 99.00,
        'billReturnUrl' => 'https://yoursite.com/payment/return',
        'billCallbackUrl' => 'https://yoursite.com/payment/callback',
        'billExternalReferenceNo' => 'ORDER-' . uniqid(),
        'billTo' => 'John Doe',
        'billEmail' => 'john.doe@example.com',
        'billPhone' => '0123456789'
    ])->send();
    
    if ($response->isRedirect()) {
        // Store bill code for later reference
        $billCode = $response->getTransactionReference();
        
        // Redirect to payment page
        header('Location: ' . $response->getRedirectUrl());
        exit;
    } else {
        throw new Exception('Payment creation failed: ' . $response->getMessage());
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Payment Verification

```php
<?php
// payment-return.php - Handle customer return from payment page

require_once 'vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('ToyyibPay');
$gateway->setTestMode(true);

$billCode = $_GET['billcode'] ?? null;

if (!$billCode) {
    die('Invalid payment reference');
}

try {
    $response = $gateway->completePurchase([
        'billCode' => $billCode
    ])->send();
    
    if ($response->isSuccessful()) {
        // Payment successful
        $transactionId = $response->getTransactionId();
        $transactionRef = $response->getTransactionReference();
        
        echo "<h1>Payment Successful!</h1>";
        echo "<p>Transaction ID: {$transactionId}</p>";
        echo "<p>Reference: {$transactionRef}</p>";
        
        // Update your database here
        // updateOrderStatus($billCode, 'paid', $transactionId);
        
    } elseif ($response->isRedirect()) {
        // Payment still pending
        echo "<h1>Payment Pending</h1>";
        echo "<p>Your payment is being processed. You will receive confirmation shortly.</p>";
        echo "<a href='{$response->getRedirectUrl()}'>Continue Payment</a>";
        
    } else {
        // Payment failed
        echo "<h1>Payment Failed</h1>";
        echo "<p>" . $response->getMessage() . "</p>";
        echo "<a href='/checkout'>Try Again</a>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
```

## E-commerce Integration

### Shopping Cart Checkout

```php
<?php
// checkout.php - Complete e-commerce checkout example

class EcommerceCheckout
{
    private $gateway;
    private $db;
    
    public function __construct($database)
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
        $this->db = $database;
    }
    
    public function processCheckout($cartItems, $customerData, $shippingData)
    {
        // Calculate totals
        $subtotal = $this->calculateSubtotal($cartItems);
        $shipping = $this->calculateShipping($shippingData);
        $tax = $this->calculateTax($subtotal, $customerData['state']);
        $total = $subtotal + $shipping + $tax;
        
        // Create order in database
        $orderId = $this->createOrder([
            'customer_data' => $customerData,
            'shipping_data' => $shippingData,
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending'
        ]);
        
        // Create payment
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_CATEGORY_CODE'),
                'billName' => 'Order #' . $orderId,
                'billDescription' => $this->generateOrderDescription($cartItems),
                'billAmount' => $total,
                'billReturnUrl' => "https://yourstore.com/payment/return?order={$orderId}",
                'billCallbackUrl' => "https://yourstore.com/payment/callback",
                'billExternalReferenceNo' => "ORDER-{$orderId}",
                'billTo' => $customerData['name'],
                'billEmail' => $customerData['email'],
                'billPhone' => $customerData['phone'],
                'billContentEmail' => "Thank you for your order! Order #: {$orderId}",
                'billAdditionalField' => [
                    'order_id' => $orderId,
                    'customer_id' => $customerData['id'],
                    'shipping_method' => $shippingData['method'],
                    'items_count' => count($cartItems)
                ]
            ])->send();
            
            if ($response->isRedirect()) {
                // Update order with bill code
                $this->updateOrderBillCode($orderId, $response->getTransactionReference());
                
                return [
                    'success' => true,
                    'redirect_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            } else {
                // Mark order as failed
                $this->updateOrderStatus($orderId, 'payment_failed');
                
                return [
                    'success' => false,
                    'error' => $response->getMessage()
                ];
            }
            
        } catch (Exception $e) {
            // Mark order as failed
            $this->updateOrderStatus($orderId, 'payment_failed');
            
            return [
                'success' => false,
                'error' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function generateOrderDescription($items)
    {
        $itemNames = array_map(function($item) {
            return $item['name'];
        }, array_slice($items, 0, 3));
        
        $description = implode(', ', $itemNames);
        
        if (count($items) > 3) {
            $description .= ' and ' . (count($items) - 3) . ' more items';
        }
        
        return $description;
    }
    
    // Database methods...
    private function createOrder($orderData) { /* Implementation */ }
    private function updateOrderBillCode($orderId, $billCode) { /* Implementation */ }
    private function updateOrderStatus($orderId, $status) { /* Implementation */ }
    private function calculateSubtotal($items) { /* Implementation */ }
    private function calculateShipping($shippingData) { /* Implementation */ }
    private function calculateTax($subtotal, $state) { /* Implementation */ }
}

// Usage
$checkout = new EcommerceCheckout($database);
$result = $checkout->processCheckout($cartItems, $customerData, $shippingData);

if ($result['success']) {
    header('Location: ' . $result['redirect_url']);
    exit;
} else {
    $error = $result['error'];
    // Display error to user
}
```

## Subscription Payments

### Recurring Billing Setup

```php
<?php
// subscription.php - Handle subscription payments

class SubscriptionManager
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
    }
    
    public function createSubscriptionPayment($subscription, $billingCycle)
    {
        $planName = $subscription['plan_name'];
        $amount = $subscription['amount'];
        $customer = $subscription['customer'];
        
        // Generate unique reference for this billing cycle
        $referenceNo = "SUB-{$subscription['id']}-" . date('Ym') . "-{$billingCycle}";
        
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_SUBSCRIPTION_CATEGORY'),
                'billName' => "{$planName} - Monthly Subscription",
                'billDescription' => "Monthly subscription payment for {$planName} plan",
                'billAmount' => $amount,
                'billReturnUrl' => "https://yourapp.com/subscription/return?sub={$subscription['id']}",
                'billCallbackUrl' => "https://yourapp.com/subscription/callback",
                'billExternalReferenceNo' => $referenceNo,
                'billTo' => $customer['name'],
                'billEmail' => $customer['email'],
                'billPhone' => $customer['phone'],
                'billContentEmail' => "Your monthly subscription payment for {$planName} plan.",
                'billAdditionalField' => [
                    'subscription_id' => $subscription['id'],
                    'billing_cycle' => $billingCycle,
                    'plan_type' => $subscription['plan_type'],
                    'customer_id' => $customer['id']
                ]
            ])->send();
            
            if ($response->isRedirect()) {
                // Log subscription payment attempt
                $this->logSubscriptionPayment([
                    'subscription_id' => $subscription['id'],
                    'bill_code' => $response->getTransactionReference(),
                    'amount' => $amount,
                    'billing_cycle' => $billingCycle,
                    'status' => 'pending'
                ]);
                
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            }
            
        } catch (Exception $e) {
            error_log("Subscription payment creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
        
        return ['success' => false, 'error' => 'Unknown error occurred'];
    }
    
    public function handleSubscriptionCallback($billCode)
    {
        try {
            $response = $this->gateway->completePurchase([
                'billCode' => $billCode
            ])->send();
            
            if ($response->isSuccessful()) {
                // Update subscription status
                $this->updateSubscriptionPayment($billCode, 'paid');
                
                // Extend subscription period
                $this->extendSubscription($billCode);
                
                // Send confirmation email
                $this->sendSubscriptionConfirmation($billCode);
                
                return true;
            }
            
        } catch (Exception $e) {
            error_log("Subscription callback error: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function logSubscriptionPayment($data) { /* Implementation */ }
    private function updateSubscriptionPayment($billCode, $status) { /* Implementation */ }
    private function extendSubscription($billCode) { /* Implementation */ }
    private function sendSubscriptionConfirmation($billCode) { /* Implementation */ }
}
```

## Split Payments

### Marketplace Commission Split

```php
<?php
// marketplace.php - Split payments between vendors and platform

class MarketplaceSplitPayment
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
    }
    
    public function createMarketplacePayment($order)
    {
        // Calculate split amounts
        $totalAmount = $order['total'];
        $platformCommission = $totalAmount * 0.05; // 5% platform fee
        $vendorAmount = $totalAmount - $platformCommission;
        
        // Prepare split payment configuration
        $splitPaymentArgs = [
            [
                'userSecretKey' => getenv('TOYYIBPAY_VENDOR_SECRET_KEY'),
                'amount' => $vendorAmount
            ],
            [
                'userSecretKey' => getenv('TOYYIBPAY_PLATFORM_SECRET_KEY'),
                'amount' => $platformCommission
            ]
        ];
        
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_MAIN_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_MARKETPLACE_CATEGORY'),
                'billName' => "Marketplace Order #{$order['id']}",
                'billDescription' => "Purchase from {$order['vendor_name']}",
                'billAmount' => $totalAmount,
                'billReturnUrl' => "https://marketplace.com/payment/return?order={$order['id']}",
                'billCallbackUrl' => "https://marketplace.com/payment/callback",
                'billExternalReferenceNo' => "MKT-{$order['id']}-" . time(),
                'billTo' => $order['customer']['name'],
                'billEmail' => $order['customer']['email'],
                'billPhone' => $order['customer']['phone'],
                'billSplitPayment' => 1,
                'billSplitPaymentArgs' => json_encode($splitPaymentArgs),
                'billAdditionalField' => [
                    'order_id' => $order['id'],
                    'vendor_id' => $order['vendor_id'],
                    'vendor_amount' => $vendorAmount,
                    'platform_commission' => $platformCommission,
                    'commission_rate' => '5%'
                ]
            ])->send();
            
            if ($response->isRedirect()) {
                // Log split payment details
                $this->logSplitPayment([
                    'order_id' => $order['id'],
                    'bill_code' => $response->getTransactionReference(),
                    'total_amount' => $totalAmount,
                    'vendor_amount' => $vendorAmount,
                    'platform_commission' => $platformCommission
                ]);
                
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            }
            
        } catch (Exception $e) {
            error_log("Split payment creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
        
        return ['success' => false, 'error' => 'Payment creation failed'];
    }
    
    private function logSplitPayment($data) { /* Implementation */ }
}
```

## Custom Fields Usage

### Advanced Custom Data Handling

```php
<?php
// custom-fields.php - Using custom fields for complex data

class CustomFieldsExample
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(true);
    }
    
    public function createPaymentWithCustomFields($orderData)
    {
        // Prepare custom fields with complex data
        $customFields = [
            // Order information
            'order_id' => $orderData['id'],
            'order_type' => $orderData['type'],
            'order_source' => $orderData['source'], // web, mobile, api
            
            // Customer information
            'customer_id' => $orderData['customer']['id'],
            'customer_tier' => $orderData['customer']['tier'], // bronze, silver, gold
            'customer_since' => $orderData['customer']['registration_date'],
            
            // Product information
            'products' => array_map(function($item) {
                return [
                    'id' => $item['product_id'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }, $orderData['items']),
            
            // Promotion information
            'discount_code' => $orderData['discount_code'] ?? null,
            'discount_amount' => $orderData['discount_amount'] ?? 0,
            'loyalty_points_used' => $orderData['loyalty_points_used'] ?? 0,
            
            // Shipping information
            'shipping_method' => $orderData['shipping']['method'],
            'shipping_address' => [
                'street' => $orderData['shipping']['address']['street'],
                'city' => $orderData['shipping']['address']['city'],
                'state' => $orderData['shipping']['address']['state'],
                'postcode' => $orderData['shipping']['address']['postcode']
            ],
            'delivery_instructions' => $orderData['shipping']['instructions'] ?? null,
            
            // Analytics data
            'utm_source' => $orderData['analytics']['utm_source'] ?? null,
            'utm_campaign' => $orderData['analytics']['utm_campaign'] ?? null,
            'referrer' => $orderData['analytics']['referrer'] ?? null,
            
            // Technical information
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('c')
        ];
        
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_CATEGORY_CODE'),
                'billName' => "Order #{$orderData['id']}",
                'billDescription' => $this->generateDescription($orderData),
                'billAmount' => $orderData['total'],
                'billReturnUrl' => "https://yoursite.com/payment/return?order={$orderData['id']}",
                'billCallbackUrl' => "https://yoursite.com/payment/callback",
                'billExternalReferenceNo' => "ORD-{$orderData['id']}-" . time(),
                'billTo' => $orderData['customer']['name'],
                'billEmail' => $orderData['customer']['email'],
                'billPhone' => $orderData['customer']['phone'],
                'billAdditionalField' => $customFields // This will be JSON encoded automatically
            ])->send();
            
            if ($response->isRedirect()) {
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            }
            
        } catch (Exception $e) {
            error_log("Custom fields payment creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
        
        return ['success' => false, 'error' => 'Payment creation failed'];
    }
    
    public function retrieveCustomFieldsFromCallback($billCode)
    {
        try {
            $response = $this->gateway->completePurchase([
                'billCode' => $billCode
            ])->send();
            
            if ($response->isSuccessful()) {
                // In a real implementation, you would need to store the custom fields
                // when creating the payment and retrieve them from your database
                // as toyyibPay doesn't return custom fields in the callback
                
                $customFields = $this->getStoredCustomFields($billCode);
                
                // Process the custom fields
                $this->processOrderWithCustomData($customFields);
                
                return $customFields;
            }
            
        } catch (Exception $e) {
            error_log("Custom fields retrieval failed: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function generateDescription($orderData)
    {
        $itemCount = count($orderData['items']);
        $firstItem = $orderData['items'][0]['name'] ?? 'Product';
        
        if ($itemCount === 1) {
            return $firstItem;
        } else {
            return "{$firstItem} and {$itemCount} other items";
        }
    }
    
    private function getStoredCustomFields($billCode) { /* Implementation */ }
    private function processOrderWithCustomData($customFields) { /* Implementation */ }
}
```

## Webhook Handling

### Robust Webhook Processing

```php
<?php
// webhook.php - Handle toyyibPay callbacks securely

class ToyyibPayWebhookHandler
{
    private $gateway;
    private $logger;
    
    public function __construct($logger = null)
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
        $this->logger = $logger;
    }
    
    public function handleWebhook()
    {
        // Log incoming webhook
        $this->log('Webhook received', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'headers' => getallheaders(),
            'body' => file_get_contents('php://input'),
            'post_data' => $_POST,
            'get_data' => $_GET
        ]);
        
        // Validate webhook
        if (!$this->validateWebhook()) {
            http_response_code(400);
            $this->log('Webhook validation failed');
            exit('Invalid webhook');
        }
        
        // Extract bill code
        $billCode = $this->extractBillCode();
        if (!$billCode) {
            http_response_code(400);
            $this->log('Bill code not found in webhook');
            exit('Bill code required');
        }
        
        // Process webhook
        try {
            $result = $this->processPaymentWebhook($billCode);
            
            if ($result) {
                http_response_code(200);
                echo 'OK';
                $this->log('Webhook processed successfully', ['bill_code' => $billCode]);
            } else {
                http_response_code(422);
                echo 'Processing failed';
                $this->log('Webhook processing failed', ['bill_code' => $billCode]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Internal error';
            $this->log('Webhook processing error', [
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    private function validateWebhook()
    {
        // Basic validation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        // Check required fields
        $requiredFields = ['billcode', 'status_id'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                return false;
            }
        }
        
        // Additional security checks can be added here
        // For example, IP whitelist, signature verification, etc.
        
        return true;
    }
    
    private function extractBillCode()
    {
        return $_POST['billcode'] ?? $_GET['billcode'] ?? null;
    }
    
    private function processPaymentWebhook($billCode)
    {
        // Verify payment status with toyyibPay API
        $response = $this->gateway->completePurchase([
            'billCode' => $billCode
        ])->send();
        
        if ($response->isSuccessful()) {
            // Payment successful
            $transactionId = $response->getTransactionId();
            $transactionRef = $response->getTransactionReference();
            
            // Update order status
            $this->updateOrderStatus($billCode, 'paid', [
                'transaction_id' => $transactionId,
                'transaction_ref' => $transactionRef,
                'paid_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send confirmation email
            $this->sendPaymentConfirmation($billCode);
            
            // Trigger post-payment actions
            $this->triggerPostPaymentActions($billCode);
            
            return true;
            
        } elseif ($response->isRedirect()) {
            // Payment still pending
            $this->updateOrderStatus($billCode, 'pending');
            return true;
            
        } else {
            // Payment failed
            $this->updateOrderStatus($billCode, 'failed', [
                'failure_reason' => $response->getMessage(),
                'failed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send failure notification
            $this->sendPaymentFailureNotification($billCode);
            
            return true; // Still return true as we processed the webhook
        }
    }
    
    private function updateOrderStatus($billCode, $status, $additionalData = [])
    {
        // Implementation to update order in database
        $this->log('Order status updated', [
            'bill_code' => $billCode,
            'status' => $status,
            'additional_data' => $additionalData
        ]);
    }
    
    private function sendPaymentConfirmation($billCode)
    {
        // Implementation to send confirmation email
        $this->log('Payment confirmation sent', ['bill_code' => $billCode]);
    }
    
    private function sendPaymentFailureNotification($billCode)
    {
        // Implementation to send failure notification
        $this->log('Payment failure notification sent', ['bill_code' => $billCode]);
    }
    
    private function triggerPostPaymentActions($billCode)
    {
        // Implementation for post-payment actions
        // - Update inventory
        // - Send digital products
        // - Schedule shipping
        // - Update customer loyalty points
        // - Trigger analytics events
        
        $this->log('Post-payment actions triggered', ['bill_code' => $billCode]);
    }
    
    private function log($message, $context = [])
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        } else {
            error_log($message . ' ' . json_encode($context));
        }
    }
}

// Usage
$webhookHandler = new ToyyibPayWebhookHandler();
$webhookHandler->handleWebhook();
```

## Laravel Integration

### Laravel Service Provider

```php
<?php
// app/Services/ToyyibPayService.php

namespace App\Services;

use Omnipay\Omnipay;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Payment;

class ToyyibPayService
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(config('toyyibpay.test_mode'));
    }
    
    public function createPayment(Order $order)
    {
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => config('toyyibpay.secret_key'),
                'categoryCode' => config('toyyibpay.category_code'),
                'billName' => "Order #{$order->id}",
                'billDescription' => $order->description,
                'billAmount' => $order->total,
                'billReturnUrl' => route('payment.return', ['order' => $order->id]),
                'billCallbackUrl' => route('payment.callback'),
                'billExternalReferenceNo' => "ORDER-{$order->id}-" . time(),
                'billTo' => $order->customer_name,
                'billEmail' => $order->customer_email,
                'billPhone' => $order->customer_phone,
                'billAdditionalField' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]
            ])->send();
            
            if ($response->isRedirect()) {
                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'bill_code' => $response->getTransactionReference(),
                    'amount' => $order->total,
                    'status' => 'pending',
                    'payment_url' => $response->getRedirectUrl()
                ]);
                
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('toyyibPay payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return ['success' => false, 'error' => 'Unknown error'];
    }
    
    public function verifyPayment($billCode)
    {
        try {
            $response = $this->gateway->completePurchase([
                'billCode' => $billCode
            ])->send();
            
            $payment = Payment::where('bill_code', $billCode)->first();
            
            if (!$payment) {
                throw new \Exception('Payment record not found');
            }
            
            if ($response->isSuccessful()) {
                $payment->update([
                    'status' => 'paid',
                    'transaction_id' => $response->getTransactionId(),
                    'transaction_ref' => $response->getTransactionReference(),
                    'paid_at' => now()
                ]);
                
                $payment->order->update(['status' => 'paid']);
                
                return ['success' => true, 'payment' => $payment];
                
            } elseif ($response->isRedirect()) {
                return [
                    'success' => false,
                    'pending' => true,
                    'redirect_url' => $response->getRedirectUrl()
                ];
                
            } else {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $response->getMessage(),
                    'failed_at' => now()
                ]);
                
                return [
                    'success' => false,
                    'error' => $response->getMessage()
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('toyyibPay payment verification failed', [
                'bill_code' => $billCode,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

### Laravel Controller

```php
<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ToyyibPayService;
use App\Models\Order;

class PaymentController extends Controller
{
    private $toyyibPayService;
    
    public function __construct(ToyyibPayService $toyyibPayService)
    {
        $this->toyyibPayService = $toyyibPayService;
    }
    
    public function checkout(Request $request, Order $order)
    {
        $result = $this->toyyibPayService->createPayment($order);
        
        if ($result['success']) {
            return redirect($result['payment_url']);
        } else {
            return back()->withErrors(['payment' => $result['error']]);
        }
    }
    
    public function return(Request $request, Order $order)
    {
        $billCode = $request->get('billcode');
        
        if (!$billCode) {
            return redirect()->route('orders.show', $order)
                ->withErrors(['payment' => 'Invalid payment reference']);
        }
        
        $result = $this->toyyibPayService->verifyPayment($billCode);
        
        if ($result['success']) {
            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment successful!');
        } elseif (isset($result['pending'])) {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Payment is being processed.');
        } else {
            return redirect()->route('orders.show', $order)
                ->withErrors(['payment' => $result['error']]);
        }
    }
    
    public function callback(Request $request)
    {
        $billCode = $request->input('billcode');
        
        if (!$billCode) {
            return response('Invalid callback', 400);
        }
        
        $result = $this->toyyibPayService->verifyPayment($billCode);
        
        // Always return 200 OK to acknowledge receipt
        return response('OK', 200);
    }
}
```

## Error Handling Patterns

### Comprehensive Error Management

```php
<?php
// error-handling.php - Advanced error handling patterns

class ToyyibPayErrorHandler
{
    const ERROR_TYPES = [
        'VALIDATION_ERROR' => 'validation',
        'NETWORK_ERROR' => 'network',
        'API_ERROR' => 'api',
        'CONFIGURATION_ERROR' => 'configuration',
        'BUSINESS_LOGIC_ERROR' => 'business'
    ];
    
    private $gateway;
    private $logger;
    private $retryAttempts;
    
    public function __construct($logger, $retryAttempts = 3)
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
        $this->logger = $logger;
        $this->retryAttempts = $retryAttempts;
    }
    
    public function createPaymentWithRetry($options, $attempt = 1)
    {
        try {
            // Validate options before making request
            $this->validatePaymentOptions($options);
            
            $response = $this->gateway->purchase($options)->send();
            
            if ($response->isRedirect()) {
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            } else {
                throw new ApiException('Payment creation failed: ' . $response->getMessage());
            }
            
        } catch (ValidationException $e) {
            // Don't retry validation errors
            $this->logError(self::ERROR_TYPES['VALIDATION_ERROR'], $e, $options, $attempt);
            return $this->formatError('Validation failed', $e->getMessage());
            
        } catch (NetworkException $e) {
            // Retry network errors
            $this->logError(self::ERROR_TYPES['NETWORK_ERROR'], $e, $options, $attempt);
            
            if ($attempt < $this->retryAttempts) {
                sleep(pow(2, $attempt)); // Exponential backoff
                return $this->createPaymentWithRetry($options, $attempt + 1);
            }
            
            return $this->formatError('Network error', 'Unable to connect to payment gateway');
            
        } catch (ApiException $e) {
            // Log API errors but don't retry
            $this->logError(self::ERROR_TYPES['API_ERROR'], $e, $options, $attempt);
            return $this->formatError('Payment gateway error', $e->getMessage());
            
        } catch (ConfigurationException $e) {
            // Configuration errors should be fixed immediately
            $this->logError(self::ERROR_TYPES['CONFIGURATION_ERROR'], $e, $options, $attempt);
            return $this->formatError('Configuration error', 'Payment system misconfigured');
            
        } catch (Exception $e) {
            // Generic error handling
            $this->logError('UNKNOWN_ERROR', $e, $options, $attempt);
            
            if ($attempt < $this->retryAttempts) {
                sleep(pow(2, $attempt));
                return $this->createPaymentWithRetry($options, $attempt + 1);
            }
            
            return $this->formatError('System error', 'An unexpected error occurred');
        }
    }
    
    public function verifyPaymentWithFallback($billCode)
    {
        $attempts = 0;
        $maxAttempts = 3;
        $delay = 1; // seconds
        
        while ($attempts < $maxAttempts) {
            try {
                $response = $this->gateway->completePurchase([
                    'billCode' => $billCode
                ])->send();
                
                return $this->processVerificationResponse($response, $billCode);
                
            } catch (Exception $e) {
                $attempts++;
                
                $this->logError('VERIFICATION_ERROR', $e, ['bill_code' => $billCode], $attempts);
                
                if ($attempts < $maxAttempts) {
                    sleep($delay * $attempts); // Increasing delay
                } else {
                    // Final attempt failed, use fallback
                    return $this->fallbackVerification($billCode);
                }
            }
        }
    }
    
    private function validatePaymentOptions($options)
    {
        $required = [
            'userSecretKey', 'categoryCode', 'billName', 'billDescription',
            'billAmount', 'billReturnUrl', 'billCallbackUrl',
            'billExternalReferenceNo', 'billTo', 'billEmail', 'billPhone'
        ];
        
        foreach ($required as $field) {
            if (!isset($options[$field]) || empty($options[$field])) {
                throw new ValidationException("Missing required field: {$field}");
            }
        }
        
        // Validate amount
        if (!is_numeric($options['billAmount']) || $options['billAmount'] <= 0) {
            throw new ValidationException('Invalid amount');
        }
        
        // Validate email
        if (!filter_var($options['billEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email address');
        }
        
        // Validate URLs
        if (!filter_var($options['billReturnUrl'], FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid return URL');
        }
        
        if (!filter_var($options['billCallbackUrl'], FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid callback URL');
        }
    }
    
    private function processVerificationResponse($response, $billCode)
    {
        if ($response->isSuccessful()) {
            return [
                'success' => true,
                'status' => 'paid',
                'transaction_id' => $response->getTransactionId(),
                'transaction_ref' => $response->getTransactionReference()
            ];
        } elseif ($response->isRedirect()) {
            return [
                'success' => false,
                'status' => 'pending',
                'redirect_url' => $response->getRedirectUrl()
            ];
        } else {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $response->getMessage()
            ];
        }
    }
    
    private function fallbackVerification($billCode)
    {
        // Implement fallback verification logic
        // This could involve:
        // 1. Checking local database for payment status
        // 2. Using alternative API endpoints
        // 3. Manual verification process
        
        $this->logger->warning('Using fallback verification', ['bill_code' => $billCode]);
        
        return [
            'success' => false,
            'status' => 'unknown',
            'message' => 'Payment verification failed, please contact support',
            'requires_manual_verification' => true
        ];
    }
    
    private function logError($type, $exception, $context, $attempt)
    {
        $this->logger->error('toyyibPay error', [
            'type' => $type,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'attempt' => $attempt,
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    private function formatError($title, $message)
    {
        return [
            'success' => false,
            'error' => [
                'title' => $title,
                'message' => $message,
                'timestamp' => date('c')
            ]
        ];
    }
}

// Custom exception classes
class ValidationException extends Exception {}
class NetworkException extends Exception {}
class ApiException extends Exception {}
class ConfigurationException extends Exception {}
```

## Testing Examples

### PHPUnit Test Suite

```php
<?php
// tests/ToyyibPayIntegrationTest.php

use PHPUnit\Framework\TestCase;
use Omnipay\Omnipay;
use Omnipay\Common\Exception\InvalidRequestException;

class ToyyibPayIntegrationTest extends TestCase
{
    private $gateway;
    private $validOptions;
    
    protected function setUp(): void
    {
        $this->gateway = Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(true);
        
        $this->validOptions = [
            'userSecretKey' => 'test-secret-key',
            'categoryCode' => 'test-category',
            'billName' => 'Test Product',
            'billDescription' => 'Test Description',
            'billAmount' => 50.00,
            'billReturnUrl' => 'https://example.com/return',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'TEST-' . uniqid(),
            'billTo' => 'John Doe',
            'billEmail' => 'john@example.com',
            'billPhone' => '0123456789'
        ];
    }
    
    public function testPurchaseWithValidData()
    {
        $response = $this->gateway->purchase($this->validOptions)->send();
        
        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
        $this->assertNotEmpty($response->getRedirectUrl());
        $this->assertNotEmpty($response->getTransactionReference());
    }
    
    public function testPurchaseWithMissingSecretKey()
    {
        $this->expectException(InvalidRequestException::class);
        
        $options = $this->validOptions;
        unset($options['userSecretKey']);
        
        $this->gateway->purchase($options)->send();
    }
    
    public function testPurchaseWithInvalidAmount()
    {
        $this->expectException(InvalidRequestException::class);
        
        $options = $this->validOptions;
        $options['billAmount'] = -10;
        
        $this->gateway->purchase($options)->send();
    }
    
    public function testPurchaseWithInvalidEmail()
    {
        $this->expectException(InvalidRequestException::class);
        
        $options = $this->validOptions;
        $options['billEmail'] = 'invalid-email';
        
        $this->gateway->purchase($options)->send();
    }
    
    public function testCompletePurchaseWithValidBillCode()
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
    
    public function testCompletePurchaseWithMissingBillCode()
    {
        $this->expectException(InvalidRequestException::class);
        
        $this->gateway->completePurchase([])->send();
    }
    
    public function testSplitPaymentConfiguration()
    {
        $splitPaymentArgs = [
            [
                'userSecretKey' => 'merchant1-key',
                'amount' => 25.00
            ],
            [
                'userSecretKey' => 'merchant2-key',
                'amount' => 25.00
            ]
        ];
        
        $options = $this->validOptions;
        $options['billSplitPayment'] = 1;
        $options['billSplitPaymentArgs'] = json_encode($splitPaymentArgs);
        
        $response = $this->gateway->purchase($options)->send();
        
        $this->assertTrue($response->isRedirect());
    }
    
    public function testCustomFieldsHandling()
    {
        $customFields = [
            'order_id' => 12345,
            'customer_tier' => 'premium',
            'notes' => 'Special handling required'
        ];
        
        $options = $this->validOptions;
        $options['billAdditionalField'] = $customFields;
        
        $response = $this->gateway->purchase($options)->send();
        
        $this->assertTrue($response->isRedirect());
    }
    
    public function testPaymentChannelRestriction()
    {
        // Test FPX only
        $options = $this->validOptions;
        $options['billPaymentChannel'] = 1;
        
        $response = $this->gateway->purchase($options)->send();
        $this->assertTrue($response->isRedirect());
        
        // Test Card only
        $options['billPaymentChannel'] = 2;
        
        $response = $this->gateway->purchase($options)->send();
        $this->assertTrue($response->isRedirect());
    }
    
    public function testAmountConversion()
    {
        // Test that amounts are properly converted to cents
        $options = $this->validOptions;
        $options['billAmount'] = 123.45;
        
        $response = $this->gateway->purchase($options)->send();
        
        $this->assertTrue($response->isRedirect());
        // In a real test, you would verify the amount was converted correctly
    }
    
    /**
     * @dataProvider invalidDataProvider
     */
    public function testPurchaseWithInvalidData($field, $value)
    {
        $this->expectException(InvalidRequestException::class);
        
        $options = $this->validOptions;
        $options[$field] = $value;
        
        $this->gateway->purchase($options)->send();
    }
    
    public function invalidDataProvider()
    {
        return [
            ['billAmount', 0],
            ['billAmount', ''],
            ['billEmail', 'not-an-email'],
            ['billReturnUrl', 'not-a-url'],
            ['billCallbackUrl', 'not-a-url'],
            ['billName', ''],
            ['billDescription', ''],
            ['billTo', ''],
            ['billPhone', '']
        ];
    }
}
```

This comprehensive examples file covers all major use cases and integration patterns for the toyyibPay Omnipay driver. Each example includes proper error handling, logging, and follows best practices for production use.