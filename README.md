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

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/sitehandy/omnipay-toyyibpay/issues),
or better yet, fork the library and submit a pull request.