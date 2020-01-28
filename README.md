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

## Category & Bill
Each payment is a `Bill`, which is under a `Category`. To begin, you need to register at toyyibPay and create a Category, then retrieve the `Category Code` for gateway setup.

## Example

### Create a purchase request
https://toyyibpay.com/apireference/

The example below explains how you can create a Bill using a Category, then redirect user to the payment page.

```php
$gateway = Omnipay::create('ToyyibPay');

$gateway->setTestMode(1); // use sandbox mode for testing

$options = [
    'userSecretKey' => '25Axq-efug-ob1j-8fsx-aswaa12sa',
    'categoryCode' => '7586hgba1',
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
    'billPaymentCharge'=> 1,
    'billContentEmail' => 'Sample email content'
];

$response = $gateway->createBill($options)->send();

if ($response->isSuccessful())
{
    // This will not happen (bill payment status is pending)
}
elseif ($response->isRedirect())
{
    // Redirect to offsite payment gateway
    $response->redirect();
}
else
{
    // API failed: display message to customer
    exit($response->getMessage());
}
```


### To manually retrieve Bill payment status
https://toyyibpay.com/apireference/

The code below gives an example how to manually query the bill status from the payment gateway server:

```php
$gateway = Omnipay::create('ToyyibPay');

$options = [
    'billCode' => 'samplecode'
];

$response = $gateway->completeCreateBill($options)->send();

if ($response->isSuccessful())
{
	// Update your DB etc and return response
    return 'The payment was successful';
}
elseif ($response->isRedirect())
{
    // Redirect to offsite payment gateway if payment status is pending    
    $response->redirect();
}
else
{
	// API failed: display message to customer
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