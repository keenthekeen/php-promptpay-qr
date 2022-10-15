# php-promptpay-qr
[![Latest Version on Packagist](https://img.shields.io/packagist/v/keenthekeen/php-promptpay-qr.svg?style=flat-square)](https://packagist.org/packages/keenthekeen/php-promptpay-qr)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/keenthekeen/php-promptpay-qr/run-tests?label=tests)](https://github.com/keenthekeen/php-promptpay-qr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/keenthekeen/php-promptpay-qr/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/keenthekeen/php-promptpay-qr/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

PHP Library to generate QR Code payload for PromptPay, a Thai QR Code Standard for Payment Transactions

inspired by [dtinth/promptpay-qr](https://github.com/dtinth/promptpay-qr) and [pheerathach/promptpay](https://github.com/pheerathach/promptpay)

adapted from [kittinan/php-promptpay-qr](https://github.com/kittinan/php-promptpay-qr)

## Installation

You can install the package via composer:

```bash
composer require keenthekeen/php-promptpay-qr
```

## Usage

```php
$pp = new \PromptPayQR\Generator();

// Generate PromptPay Payload
Builder::staticMerchantPresentedQR('0899999999')->build()
// 00020101021129370016A000000677010111011300668999999995802TH53037646304FE29

// Generate PromptPay Payload With Amount
Builder::staticMerchantPresentedQR('089-999-9999')->setAmount(420)->build()
// 00020101021229370016A000000677010111011300668999999995802TH53037645406420.006304CF9E

// Generate QR Code SVG string (to be return as HTTP response with header Content-Type: image/svg+xml)
Builder::staticMerchantPresentedQR('1-2345-67890-12-3')->toSvgString()

// Generate QR Code SVG file
Builder::staticMerchantPresentedQR('1-2345-67890-12-3')->toSvgFile($path)
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
