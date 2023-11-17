<?php

use PromptPayQR\Builder;
use PromptPayQR\Helper;

test('formatTarget works', function () {
    expect(Helper::formatTarget('0899999999'))
        ->toEqual('0066899999999');
    expect(Helper::formatTarget('089-999-9999'))
        ->toEqual('0066899999999');
    expect(Helper::formatTarget('1234567890123'))
        ->toEqual('1234567890123');
    expect(Helper::formatTarget('0123456789012'))
        ->toEqual('0123456789012');
});

test('formatAmount works', function () {
    expect(Helper::formatAmount(1337.1337))
        ->toEqual('1337.13');
    expect(Helper::formatAmount(1337.1387))
        ->toEqual('1337.14');
});

test('Crc16 works', function () {
    expect(Helper::crc16('00020101021129370016A000000677010111011300660000000005802TH53037646304'))
        ->toEqual('8956');
    expect(Helper::crc16('00020101021129370016A000000677010111011300668999999995802TH53037646304'))
        ->toEqual('FE29');
});

test('F works', function () {
    expect(Helper::f('00', '01'))
        ->toEqual('000201');
    expect(Helper::f('05', '420'))
        ->toEqual('0503420');
});

test('Build works', function () {
    expect(Builder::staticMerchantPresentedQR('0899999999')->build())
        ->toEqual('00020101021129370016A0000006770101110113006689999999953037645802TH63042195');
    expect(Builder::staticQR()->creditTransfer()->setMerchantIdentifier('0891234567')->build())
        ->toEqual('00020101021129370016A0000006770101110113006689123456753037645802TH6304F67D');
    expect(Builder::staticMerchantPresentedQR('0000000000')->build())
        ->toEqual('00020101021129370016A0000006770101110113006600000000053037645802TH630456EA');
    expect(Builder::staticMerchantPresentedQR('1234567890123')->build())
        ->toEqual('00020101021129370016A0000006770101110213123456789012353037645802TH630433FC');
    expect(Builder::staticMerchantPresentedQR('089-123-4567')->setAmount('13371337.75')->build())
        ->toEqual('00020101021129370016A0000006770101110113006689123456753037645802TH541113371337.756304C129');
    expect(Builder::dynamicQR()->creditTransfer()->setMerchantIdentifier('089-123-4567')->setAmount('13371337.75')->build())
        ->toEqual('00020101021229370016A0000006770101110113006689123456753037645802TH541113371337.756304C21C');
    expect(Builder::dynamicQR()->creditTransfer()->setMerchantIdentifier('1234567890123')->setAmount(420)->build())
        ->toEqual('00020101021229370016A0000006770101110213123456789012353037645802TH5406420.00630415D2');
    expect(Builder::staticMerchantPresentedQR('004999000288505')->build()) // K PLUS ID
        ->toEqual('00020101021129390016A000000677010111031500499900028850553037645802TH6304CA9D');
    expect(Builder::dynamicQR()->creditTransfer()->setMerchantIdentifier('004999000288505')->setAmount('100.25')->build()) // K PLUS ID
        ->toEqual('00020101021229390016A000000677010111031500499900028850553037645802TH5406100.2563049C33');
    expect(Builder::staticMerchantPresentedQR('004000006579718')->build()) // K PLUS Shop
        ->toEqual('00020101021129390016A000000677010111031500400000657971853037645802TH63042409');
    expect(Builder::dynamicQR()->creditTransfer()->setMerchantIdentifier('004000006579718')->setAmount(200.50)->build())
        ->toEqual('00020101021229390016A000000677010111031500400000657971853037645802TH5406200.506304209E');
});

test('toSvgString works', function () {
    expect(Builder::dynamicQR()->creditTransfer()->setMerchantIdentifier('004000006579718')->setAmount(200.50)->toSvgString())
        ->toBeString();
});
