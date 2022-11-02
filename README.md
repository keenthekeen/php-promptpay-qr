# php-promptpay-qr
[![Latest Version on Packagist](https://img.shields.io/packagist/v/keenthekeen/php-promptpay-qr.svg?style=flat-square)](https://packagist.org/packages/keenthekeen/php-promptpay-qr)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/keenthekeen/php-promptpay-qr/php?style=flat-square)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/keenthekeen/php-promptpay-qr/run-tests?label=tests&style=flat-square)](https://github.com/keenthekeen/php-promptpay-qr/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/keenthekeen/php-promptpay-qr/Fix%20PHP%20code%20style%20issues?label=code%20style&style=flat-square)](https://github.com/keenthekeen/php-promptpay-qr/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amaster)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](https://opensource.org/licenses/MIT)

PHP Library to generate QR Code payload for PromptPay, a Thai QR Code Standard for Payment Transactions, using fluent interface

inspired by [dtinth/promptpay-qr](https://github.com/dtinth/promptpay-qr) and [pheerathach/promptpay](https://github.com/pheerathach/promptpay)

adapted from [kittinan/php-promptpay-qr](https://github.com/kittinan/php-promptpay-qr)

## Installation

You can install the package via composer:

```bash
composer require keenthekeen/php-promptpay-qr
```

## Usage

```php
use PromptPayQR\Builder;

// Generate PromptPay Payload
Builder::staticMerchantPresentedQR('0899999999')->build();
// 00020101021129370016A000000677010111011300668999999995802TH53037646304FE29

// Generate PromptPay Payload With Amount
Builder::staticMerchantPresentedQR('089-999-9999')->setAmount(420)->build();
// 00020101021229370016A000000677010111011300668999999995802TH53037645406420.006304CF9E

// Generate PromptPay Payload With Amount (one-time use)
Builder::dynamicQR()->creditTransfer()->phoneNumber('083-888-3333')->setAmount(420)->build();

// Generate PromptPay Bill Payment (Tag 30) Payload
Builder::dynamicQR()->billPayment()
  ->setBillerIdentifier('099400015804189', 'Ref1', 'Ref2')
  ->setAmount(1999.99)->build();

// Generate QR Code SVG string
$svgString = Builder::staticMerchantPresentedQR('1-2345-67890-12-3')->toSvgString();
// Laravel example: respond with header Content-Type: image/svg+xml
return response($svgString, 200)->header('Content-Type', 'image/svg+xml')->header('Cache-Control', 'no-store');

// Generate QR Code SVG file
Builder::staticMerchantPresentedQR('1-2345-67890-12-3')->toSvgFile($path);
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
