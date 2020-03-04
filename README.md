# Omnipay: toyyibPay

**toyyibPay driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements toyyibPay support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `sitehandy/omnipay-toyyibpay` with Composer:

```
composer require league/omnipay sitehandy/omnipay-toyyibpay
```

## Basic Usage

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## toyyibPay Category & Bill

Each payment is a `Bill`, which is under a `Category`. To begin, you need to open an account at [toyyibPay.com](https://toyyibpay.com/e/2238297686400) and then create a Category. Then, retrieve the `Category Code` for the gateway setup.

## Example

### How to create a Bill

Reference: https://toyyibpay.com/apireference/#cb

Bill serves as an invoice to your customer. The example below explains how you can create a toyyibPay Bill and then redirect user to the payment page.

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

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/sitehandy/omnipay-toyyibpay/issues),
or better yet, fork the library and submit a pull request.