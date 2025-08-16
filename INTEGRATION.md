# toyyibPay Integration Guide

This guide provides step-by-step instructions for integrating toyyibPay with your PHP application using the Omnipay framework.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Account Setup](#account-setup)
- [Installation](#installation)
- [Basic Integration](#basic-integration)
- [Advanced Integration](#advanced-integration)
- [Production Deployment](#production-deployment)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

- PHP 7.4 or higher (PHP 8.0+ recommended)
- cURL extension enabled
- JSON extension enabled
- OpenSSL extension enabled
- Composer for dependency management

### Check Your Environment

```bash
# Check PHP version
php --version

# Check required extensions
php -m | grep -E "curl|json|openssl"

# Check Composer
composer --version
```

## Account Setup

### Step 1: Create toyyibPay Account

1. Visit [toyyibPay.com](https://toyyibpay.com/)
2. Click "Sign Up" to create a new account
3. Complete the registration process
4. Verify your email address
5. Complete your profile and business information

### Step 2: Create a Category

1. Log in to your toyyibPay dashboard
2. Navigate to "Category" section
3. Click "Add Category"
4. Fill in the category details:
   - Category Name: e.g., "Online Store"
   - Description: Brief description of your business
   - Category Type: Select appropriate type
5. Save the category and note down the **Category Code**

### Step 3: Get API Credentials

1. Go to "Settings" > "API Settings"
2. Copy your **Secret Key**
3. Note your **Category Code** from the previous step
4. For testing, use the sandbox environment

### Step 4: Configure Webhooks (Optional but Recommended)

1. In API Settings, set up webhook URLs:
   - Return URL: `https://yoursite.com/payment/return`
   - Callback URL: `https://yoursite.com/payment/callback`
2. Ensure these URLs are publicly accessible

## Installation

### Step 1: Install via Composer

```bash
# Navigate to your project directory
cd /path/to/your/project

# Install Omnipay and toyyibPay driver
composer require league/omnipay sitehandy/omnipay-toyyibpay:^1.0
```

### Step 2: Verify Installation

```php
<?php
// test-installation.php
require_once 'vendor/autoload.php';

use Omnipay\Omnipay;

try {
    $gateway = Omnipay::create('ToyyibPay');
    echo "✅ toyyibPay driver installed successfully!\n";
    echo "Gateway class: " . get_class($gateway) . "\n";
} catch (Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
}
```

### Step 3: Environment Configuration

Create a `.env` file in your project root:

```env
# toyyibPay Configuration
TOYYIBPAY_TEST_MODE=true
TOYYIBPAY_SECRET_KEY=your-secret-key-here
TOYYIBPAY_CATEGORY_CODE=your-category-code-here

# URLs
APP_URL=https://yoursite.com
TOYYIBPAY_RETURN_URL=${APP_URL}/payment/return
TOYYIBPAY_CALLBACK_URL=${APP_URL}/payment/callback
```

## Basic Integration

### Step 1: Create Payment Gateway Class

```php
<?php
// src/PaymentGateway.php

class PaymentGateway
{
    private $gateway;
    
    public function __construct()
    {
        $this->gateway = \Omnipay\Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
    }
    
    public function createPayment($orderData)
    {
        try {
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_CATEGORY_CODE'),
                'billName' => $orderData['product_name'],
                'billDescription' => $orderData['description'],
                'billAmount' => $orderData['amount'],
                'billReturnUrl' => getenv('TOYYIBPAY_RETURN_URL'),
                'billCallbackUrl' => getenv('TOYYIBPAY_CALLBACK_URL'),
                'billExternalReferenceNo' => $orderData['order_id'],
                'billTo' => $orderData['customer_name'],
                'billEmail' => $orderData['customer_email'],
                'billPhone' => $orderData['customer_phone']
            ])->send();
            
            if ($response->isRedirect()) {
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $response->getTransactionReference()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->getMessage()
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function verifyPayment($billCode)
    {
        try {
            $response = $this->gateway->completePurchase([
                'billCode' => $billCode
            ])->send();
            
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
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

### Step 2: Create Checkout Page

```php
<?php
// checkout.php
require_once 'vendor/autoload.php';
require_once 'src/PaymentGateway.php';

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = [
        'order_id' => 'ORDER-' . uniqid(),
        'product_name' => $_POST['product_name'],
        'description' => $_POST['description'],
        'amount' => floatval($_POST['amount']),
        'customer_name' => $_POST['customer_name'],
        'customer_email' => $_POST['customer_email'],
        'customer_phone' => $_POST['customer_phone']
    ];
    
    $gateway = new PaymentGateway();
    $result = $gateway->createPayment($orderData);
    
    if ($result['success']) {
        // Store bill code in session for later reference
        session_start();
        $_SESSION['bill_code'] = $result['bill_code'];
        $_SESSION['order_data'] = $orderData;
        
        // Redirect to payment page
        header('Location: ' . $result['payment_url']);
        exit;
    } else {
        $error = $result['error'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Checkout</h1>
    
    <?php if (isset($error)): ?>
        <div class="error">Error: <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required value="Premium Software License">
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required>1-year premium software license with support</textarea>
        </div>
        
        <div class="form-group">
            <label for="amount">Amount (RM):</label>
            <input type="number" id="amount" name="amount" step="0.01" required value="99.00">
        </div>
        
        <div class="form-group">
            <label for="customer_name">Your Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
        </div>
        
        <div class="form-group">
            <label for="customer_email">Email:</label>
            <input type="email" id="customer_email" name="customer_email" required>
        </div>
        
        <div class="form-group">
            <label for="customer_phone">Phone:</label>
            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="0123456789">
        </div>
        
        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
```

### Step 3: Create Return Page

```php
<?php
// payment/return.php
require_once '../vendor/autoload.php';
require_once '../src/PaymentGateway.php';

// Load environment variables
if (file_exists('../.env')) {
    $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

session_start();

$billCode = $_GET['billcode'] ?? null;

if (!$billCode) {
    die('Invalid payment reference');
}

$gateway = new PaymentGateway();
$result = $gateway->verifyPayment($billCode);

$orderData = $_SESSION['order_data'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Result</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
        .success { color: green; }
        .error { color: red; }
        .pending { color: orange; }
        .details { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 4px; text-align: left; }
    </style>
</head>
<body>
    <?php if ($result['success'] && $result['status'] === 'paid'): ?>
        <h1 class="success">✅ Payment Successful!</h1>
        <p>Thank you for your payment. Your transaction has been completed successfully.</p>
        
        <div class="details">
            <h3>Payment Details:</h3>
            <p><strong>Product:</strong> <?= htmlspecialchars($orderData['product_name'] ?? 'N/A') ?></p>
            <p><strong>Amount:</strong> RM <?= number_format($orderData['amount'] ?? 0, 2) ?></p>
            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($result['transaction_id']) ?></p>
            <p><strong>Reference:</strong> <?= htmlspecialchars($result['transaction_ref']) ?></p>
        </div>
        
    <?php elseif ($result['status'] === 'pending'): ?>
        <h1 class="pending">⏳ Payment Pending</h1>
        <p>Your payment is being processed. You will receive confirmation shortly.</p>
        
        <?php if (isset($result['redirect_url'])): ?>
            <p><a href="<?= htmlspecialchars($result['redirect_url']) ?>">Continue Payment</a></p>
        <?php endif; ?>
        
    <?php else: ?>
        <h1 class="error">❌ Payment Failed</h1>
        <p><?= htmlspecialchars($result['message'] ?? $result['error'] ?? 'Payment could not be processed') ?></p>
        <p><a href="../checkout.php">Try Again</a></p>
    <?php endif; ?>
    
    <p><a href="../">Return to Home</a></p>
</body>
</html>
```

### Step 4: Create Callback Handler

```php
<?php
// payment/callback.php
require_once '../vendor/autoload.php';
require_once '../src/PaymentGateway.php';

// Load environment variables
if (file_exists('../.env')) {
    $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Log callback for debugging
error_log('toyyibPay callback received: ' . print_r($_POST, true));

$billCode = $_POST['billcode'] ?? null;

if (!$billCode) {
    http_response_code(400);
    exit('Bill code required');
}

try {
    $gateway = new PaymentGateway();
    $result = $gateway->verifyPayment($billCode);
    
    if ($result['success'] && $result['status'] === 'paid') {
        // Payment successful - update your database here
        error_log("Payment successful for bill code: {$billCode}");
        
        // Example: Update order status in database
        // updateOrderStatus($billCode, 'paid', $result['transaction_id']);
        
        // Send confirmation email
        // sendPaymentConfirmation($billCode);
    } else {
        error_log("Payment not successful for bill code: {$billCode}. Status: " . ($result['status'] ?? 'unknown'));
    }
    
    // Always respond with 200 OK to acknowledge receipt
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Callback processing error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}
```

## Advanced Integration

### Database Integration

#### Step 1: Create Database Tables

```sql
-- orders.sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(100) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    bill_code VARCHAR(100),
    transaction_id VARCHAR(100),
    transaction_ref VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(100) NOT NULL,
    bill_code VARCHAR(100),
    action VARCHAR(50) NOT NULL,
    status VARCHAR(50),
    response_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_bill_code (bill_code)
);
```

#### Step 2: Enhanced Payment Gateway with Database

```php
<?php
// src/EnhancedPaymentGateway.php

class EnhancedPaymentGateway
{
    private $gateway;
    private $pdo;
    
    public function __construct($database)
    {
        $this->gateway = \Omnipay\Omnipay::create('ToyyibPay');
        $this->gateway->setTestMode(getenv('TOYYIBPAY_TEST_MODE') === 'true');
        $this->pdo = $database;
    }
    
    public function createOrder($orderData)
    {
        // Insert order into database
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, 
                              product_name, description, amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $orderData['order_id'],
            $orderData['customer_name'],
            $orderData['customer_email'],
            $orderData['customer_phone'],
            $orderData['product_name'],
            $orderData['description'],
            $orderData['amount']
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    public function createPayment($orderData)
    {
        try {
            // Create order in database
            $orderId = $this->createOrder($orderData);
            
            // Create payment with toyyibPay
            $response = $this->gateway->purchase([
                'userSecretKey' => getenv('TOYYIBPAY_SECRET_KEY'),
                'categoryCode' => getenv('TOYYIBPAY_CATEGORY_CODE'),
                'billName' => $orderData['product_name'],
                'billDescription' => $orderData['description'],
                'billAmount' => $orderData['amount'],
                'billReturnUrl' => getenv('TOYYIBPAY_RETURN_URL'),
                'billCallbackUrl' => getenv('TOYYIBPAY_CALLBACK_URL'),
                'billExternalReferenceNo' => $orderData['order_id'],
                'billTo' => $orderData['customer_name'],
                'billEmail' => $orderData['customer_email'],
                'billPhone' => $orderData['customer_phone'],
                'billAdditionalField' => [
                    'internal_order_id' => $orderId,
                    'order_reference' => $orderData['order_id']
                ]
            ])->send();
            
            if ($response->isRedirect()) {
                $billCode = $response->getTransactionReference();
                
                // Update order with bill code
                $this->updateOrderBillCode($orderData['order_id'], $billCode);
                
                // Log payment creation
                $this->logPaymentAction($orderData['order_id'], $billCode, 'payment_created', 'pending', [
                    'payment_url' => $response->getRedirectUrl()
                ]);
                
                return [
                    'success' => true,
                    'payment_url' => $response->getRedirectUrl(),
                    'bill_code' => $billCode,
                    'order_id' => $orderId
                ];
            } else {
                // Log payment creation failure
                $this->logPaymentAction($orderData['order_id'], null, 'payment_creation_failed', 'failed', [
                    'error' => $response->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'error' => $response->getMessage()
                ];
            }
            
        } catch (Exception $e) {
            // Log exception
            $this->logPaymentAction($orderData['order_id'] ?? null, null, 'payment_exception', 'error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function verifyPayment($billCode)
    {
        try {
            $response = $this->gateway->completePurchase([
                'billCode' => $billCode
            ])->send();
            
            $order = $this->getOrderByBillCode($billCode);
            
            if ($response->isSuccessful()) {
                // Update order status
                $this->updateOrderStatus($billCode, 'paid', [
                    'transaction_id' => $response->getTransactionId(),
                    'transaction_ref' => $response->getTransactionReference()
                ]);
                
                // Log successful payment
                $this->logPaymentAction($order['order_id'], $billCode, 'payment_verified', 'paid', [
                    'transaction_id' => $response->getTransactionId(),
                    'transaction_ref' => $response->getTransactionReference()
                ]);
                
                return [
                    'success' => true,
                    'status' => 'paid',
                    'transaction_id' => $response->getTransactionId(),
                    'transaction_ref' => $response->getTransactionReference(),
                    'order' => $order
                ];
                
            } elseif ($response->isRedirect()) {
                // Log pending payment
                $this->logPaymentAction($order['order_id'], $billCode, 'payment_pending', 'pending', [
                    'redirect_url' => $response->getRedirectUrl()
                ]);
                
                return [
                    'success' => false,
                    'status' => 'pending',
                    'redirect_url' => $response->getRedirectUrl(),
                    'order' => $order
                ];
                
            } else {
                // Update order status to failed
                $this->updateOrderStatus($billCode, 'failed');
                
                // Log failed payment
                $this->logPaymentAction($order['order_id'], $billCode, 'payment_failed', 'failed', [
                    'message' => $response->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => $response->getMessage(),
                    'order' => $order
                ];
            }
            
        } catch (Exception $e) {
            // Log verification exception
            $this->logPaymentAction(null, $billCode, 'verification_exception', 'error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function updateOrderBillCode($orderId, $billCode)
    {
        $stmt = $this->pdo->prepare("UPDATE orders SET bill_code = ? WHERE order_id = ?");
        $stmt->execute([$billCode, $orderId]);
    }
    
    private function updateOrderStatus($billCode, $status, $additionalData = [])
    {
        $sql = "UPDATE orders SET status = ?";
        $params = [$status];
        
        if (isset($additionalData['transaction_id'])) {
            $sql .= ", transaction_id = ?";
            $params[] = $additionalData['transaction_id'];
        }
        
        if (isset($additionalData['transaction_ref'])) {
            $sql .= ", transaction_ref = ?";
            $params[] = $additionalData['transaction_ref'];
        }
        
        $sql .= " WHERE bill_code = ?";
        $params[] = $billCode;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    private function getOrderByBillCode($billCode)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE bill_code = ?");
        $stmt->execute([$billCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function logPaymentAction($orderId, $billCode, $action, $status, $responseData = [])
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payment_logs (order_id, bill_code, action, status, response_data) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderId,
            $billCode,
            $action,
            $status,
            json_encode($responseData)
        ]);
    }
    
    public function getOrderHistory($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payment_logs 
            WHERE order_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Configuration Management

#### Step 1: Create Configuration Class

```php
<?php
// src/Config.php

class Config
{
    private static $config = [];
    
    public static function load($configFile = '.env')
    {
        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\' ');
                    
                    // Handle boolean values
                    if (strtolower($value) === 'true') {
                        $value = true;
                    } elseif (strtolower($value) === 'false') {
                        $value = false;
                    }
                    
                    self::$config[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }
    
    public static function get($key, $default = null)
    {
        return self::$config[$key] ?? getenv($key) ?: $default;
    }
    
    public static function getToyyibPayConfig()
    {
        return [
            'test_mode' => self::get('TOYYIBPAY_TEST_MODE', true),
            'secret_key' => self::get('TOYYIBPAY_SECRET_KEY'),
            'category_code' => self::get('TOYYIBPAY_CATEGORY_CODE'),
            'return_url' => self::get('TOYYIBPAY_RETURN_URL'),
            'callback_url' => self::get('TOYYIBPAY_CALLBACK_URL')
        ];
    }
    
    public static function validate()
    {
        $required = [
            'TOYYIBPAY_SECRET_KEY',
            'TOYYIBPAY_CATEGORY_CODE',
            'TOYYIBPAY_RETURN_URL',
            'TOYYIBPAY_CALLBACK_URL'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            if (!self::get($key)) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required configuration: ' . implode(', ', $missing));
        }
        
        // Validate URLs
        $urls = ['TOYYIBPAY_RETURN_URL', 'TOYYIBPAY_CALLBACK_URL'];
        foreach ($urls as $urlKey) {
            $url = self::get($urlKey);
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid URL for {$urlKey}: {$url}");
            }
        }
        
        return true;
    }
}
```

## Production Deployment

### Step 1: Environment Setup

```env
# Production .env file
TOYYIBPAY_TEST_MODE=false
TOYYIBPAY_SECRET_KEY=your-production-secret-key
TOYYIBPAY_CATEGORY_CODE=your-production-category-code

# Production URLs
APP_URL=https://yourproductiondomain.com
TOYYIBPAY_RETURN_URL=${APP_URL}/payment/return
TOYYIBPAY_CALLBACK_URL=${APP_URL}/payment/callback

# Database
DB_HOST=your-db-host
DB_NAME=your-db-name
DB_USER=your-db-user
DB_PASS=your-db-password

# Security
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict
```

### Step 2: Security Checklist

```php
<?php
// security-check.php

class SecurityChecker
{
    public static function checkEnvironment()
    {
        $checks = [];
        
        // Check HTTPS
        $checks['https'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        
        // Check file permissions
        $checks['env_permissions'] = !is_readable('.env') || fileperms('.env') & 0044;
        
        // Check PHP version
        $checks['php_version'] = version_compare(PHP_VERSION, '7.4.0', '>=');
        
        // Check required extensions
        $requiredExtensions = ['curl', 'json', 'openssl', 'pdo'];
        $checks['extensions'] = true;
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $checks['extensions'] = false;
                break;
            }
        }
        
        // Check configuration
        try {
            Config::validate();
            $checks['configuration'] = true;
        } catch (Exception $e) {
            $checks['configuration'] = false;
        }
        
        return $checks;
    }
    
    public static function displayReport()
    {
        $checks = self::checkEnvironment();
        
        echo "Security Check Report:\n";
        echo "=====================\n";
        
        foreach ($checks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            echo "{$icon} {$check}: " . ($status ? 'PASS' : 'FAIL') . "\n";
        }
        
        $allPassed = array_reduce($checks, function($carry, $item) {
            return $carry && $item;
        }, true);
        
        echo "\nOverall Status: " . ($allPassed ? '✅ READY FOR PRODUCTION' : '❌ NEEDS ATTENTION') . "\n";
        
        return $allPassed;
    }
}

// Run security check
require_once 'src/Config.php';
Config::load();
SecurityChecker::displayReport();
```

### Step 3: Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Starting deployment..."

# Update code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Run security check
php security-check.php

# Test configuration
php -r "require 'vendor/autoload.php'; require 'src/Config.php'; Config::load(); echo 'Configuration OK\n';"

# Set proper permissions
chmod 600 .env
chmod -R 755 public/
chmod -R 644 src/

# Clear any caches if applicable
# php artisan cache:clear  # For Laravel
# php bin/console cache:clear  # For Symfony

echo "✅ Deployment completed successfully!"
```

## Testing

### Step 1: Unit Tests

```php
<?php
// tests/PaymentGatewayTest.php

use PHPUnit\Framework\TestCase;

class PaymentGatewayTest extends TestCase
{
    private $gateway;
    
    protected function setUp(): void
    {
        // Load test configuration
        Config::load('.env.test');
        
        $this->gateway = new PaymentGateway();
    }
    
    public function testCreatePaymentSuccess()
    {
        $orderData = [
            'order_id' => 'TEST-' . uniqid(),
            'product_name' => 'Test Product',
            'description' => 'Test Description',
            'amount' => 10.00,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0123456789'
        ];
        
        $result = $this->gateway->createPayment($orderData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('payment_url', $result);
        $this->assertArrayHasKey('bill_code', $result);
    }
    
    public function testCreatePaymentWithInvalidData()
    {
        $orderData = [
            'order_id' => 'TEST-' . uniqid(),
            'product_name' => '',  // Invalid: empty name
            'amount' => -10,       // Invalid: negative amount
            'customer_email' => 'invalid-email'  // Invalid: bad email
        ];
        
        $result = $this->gateway->createPayment($orderData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
}
```

### Step 2: Integration Tests

```bash
#!/bin/bash
# test-integration.sh

echo "🧪 Running integration tests..."

# Test payment creation
echo "Testing payment creation..."
php tests/integration/test-payment-creation.php

# Test callback handling
echo "Testing callback handling..."
php tests/integration/test-callback.php

# Test error scenarios
echo "Testing error scenarios..."
php tests/integration/test-errors.php

echo "✅ Integration tests completed!"
```

## Troubleshooting

### Common Issues

1. **SSL Certificate Issues**
   ```bash
   # Update CA certificates
   sudo apt-get update && sudo apt-get install ca-certificates
   
   # Or for CentOS/RHEL
   sudo yum update ca-certificates
   ```

2. **Permission Denied Errors**
   ```bash
   # Set proper file permissions
   chmod 600 .env
   chmod -R 755 public/
   chown -R www-data:www-data /path/to/your/app
   ```

3. **Callback URL Not Accessible**
   - Ensure callback URL is publicly accessible
   - Test with tools like ngrok for local development
   - Check firewall settings

4. **Invalid Secret Key**
   - Verify secret key is correct
   - Check if using sandbox vs production keys
   - Ensure category code matches the secret key

### Debug Mode

```php
<?php
// Enable debug logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/logs/toyyibpay.log');

// Add debug information to all requests
class DebugPaymentGateway extends PaymentGateway
{
    public function createPayment($orderData)
    {
        error_log('Creating payment with data: ' . json_encode($orderData));
        
        $result = parent::createPayment($orderData);
        
        error_log('Payment creation result: ' . json_encode($result));
        
        return $result;
    }
}
```

This integration guide provides a complete step-by-step process for implementing toyyibPay in your PHP application, from basic setup to production deployment with proper security measures and testing procedures.