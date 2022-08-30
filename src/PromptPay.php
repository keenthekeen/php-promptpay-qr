<?php

namespace KS;

use BaconQrCode\Renderer\Image\Png;
use mermshaus\CRC\CRC16CCITT;

/**
 * Inspired and code logic from https://github.com/dtinth/promptpay-qr
 * More information https://www.blognone.com/node/95133
 */
class PromptPay {
    public const POI_METHOD_STATIC = '11'; // shown for more than one transaction
    public const POI_METHOD_DYNAMIC = '12'; // a new QR Code is shown for each transaction.
    public const AID_CREDIT_TRANSFER = 'A000000677010111'; // default
    public const AID_BILL_PAYMENT_DOMESTIC = 'A000000677010112';
    
    protected const ID_PAYLOAD_FORMAT = '00';
    protected const ID_POI_METHOD = '01';
    protected const ID_MERCHANT_INFORMATION_BOT = '29';
    protected const ID_TRANSACTION_CURRENCY = '53';
    protected const ID_TRANSACTION_AMOUNT = '54';
    protected const ID_COUNTRY_CODE = '58';
    protected const ID_CRC = '63';
    
    protected const PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE = '01';
    protected const MERCHANT_INFORMATION_TEMPLATE_ID_GUID = '00';
    public const BOT_ID_MERCHANT_PHONE_NUMBER = '01';
    public const BOT_ID_MERCHANT_TAX_ID = '02';
    public const BOT_ID_MERCHANT_EWALLET_ID = '03';
    protected const TRANSACTION_CURRENCY_THB = '764';
    protected const COUNTRY_CODE_TH = 'TH';
    
    protected $pointOfInitiationMethod;
    protected $aid;
    protected $targetType;
    protected $target;
    protected $amount;
    protected $ref1;
    protected $ref2;
    
    public static function builder(): self {
        return new self();
    }
    
    /**
     * The Point of Initiation Method has a value of "11" for static QR Codes and a value of "12" for dynamic QR Codes.
     */
    public function setPointOfInitiationMethod(string $pointOfInitiationMethod): self {
        $this->pointOfInitiationMethod = $pointOfInitiationMethod;
        
        return $this;
    }
    
    public function setAid(string $aid): self {
        $this->aid = $aid;
        
        return $this;
    }
    
    public function setTarget(string $target, ?string $type = NULL): self {
        $this->target = $target;
        $this->targetType = $type ?? (strlen($target) >= 15 ? self::BOT_ID_MERCHANT_EWALLET_ID : (strlen($target) >= 13 ? self::BOT_ID_MERCHANT_TAX_ID : self::BOT_ID_MERCHANT_PHONE_NUMBER));
        
        return $this;
    }
    
    public function setAmount($amount = NULL): self {
        $this->amount = $amount;
        
        return $this;
    }
    
    public function setRef1($ref = NULL): self {
        $this->ref1 = $ref;
        
        return $this;
    }
    
    public function setRef2($ref = NULL): self {
        $this->ref2 = $ref;
        
        return $this;
    }
    
    
    /**
     * @throws \Exception
     */
    public function build(): string {
        if (empty($this->target)) {
            throw new \Exception('Transfer target is required.');
        }
        $merchantData = ($this->aid == self::AID_BILL_PAYMENT_DOMESTIC) ? [
            self::f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, self::AID_BILL_PAYMENT_DOMESTIC),
            self::f('01', $this->target),
            self::f('02', $this->ref1),
        ] : [
            self::f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, $this->aid ?? self::AID_CREDIT_TRANSFER),
            self::f($this->targetType, self::formatTarget($this->target))
        ];
        if ($this->aid == self::AID_BILL_PAYMENT_DOMESTIC and $this->ref2 !== NULL) {
            $merchantData[] = self::f('03', $this->ref2);
        }
        $data = [
            self::f(self::ID_PAYLOAD_FORMAT, self::PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE),
            self::f(self::ID_POI_METHOD, $this->pointOfInitiationMethod ?? ($this->amount ? self::POI_METHOD_DYNAMIC : self::POI_METHOD_STATIC)),
            self::f(self::ID_MERCHANT_INFORMATION_BOT, $this->serialize($merchantData)),
            self::f(self::ID_COUNTRY_CODE, self::COUNTRY_CODE_TH),
            self::f(self::ID_TRANSACTION_CURRENCY, self::TRANSACTION_CURRENCY_THB),
        ];
        
        if ($this->amount !== NULL) {
            // Caution: amount 0.00 will be treated as static QR
            $data[] = self::f(self::ID_TRANSACTION_AMOUNT, self::formatAmount($this->amount));
        }
        
        $data[] = self::f(self::ID_CRC, self::crc16($this->serialize($data) . self::ID_CRC . '04'));
        
        return $this->serialize($data);
    }
    
    public static function f($id, $value): string {
        return implode('', [$id, substr('00' . strlen($value), -2), $value]);
    }
    
    public static function serialize($xs): string {
        return implode('', $xs);
    }
    
    public static function sanitizeTarget($str) {
        return preg_replace('/[^0-9]/', '', $str);
    }
    
    public static function formatTarget($target) {
        $str = self::sanitizeTarget($target);
        if (strlen($str) >= 13) {
            return $str;
        }
        
        $str = preg_replace('/^0/', '66', $str);
        $str = '0000000000000' . $str;
        
        return substr($str, -13);
    }
    
    public static function formatAmount($amount): string {
        return number_format($amount, 2, '.', '');
    }
    
    public static function crc16($data): string {
        $crc16 = new CRC16CCITT();
        $crc16->update($data);
        $checksum = $crc16->finish();
        
        return strtoupper(bin2hex($checksum));
    }
    
    protected static function getPngWriter(int $width = 500) {
        $renderer = new Png();
        $renderer->setHeight($width);
        $renderer->setWidth($width);
        $renderer->setMargin(0);
        
        return new \BaconQrCode\Writer($renderer);
    }
    
    public function generateQrCodeAsFile($savePath) {
        self::getPngWriter()->writeFile($this->build(), $savePath);
    }
    
    public function generateQrCodeAsString() {
        self::getPngWriter()->writeString($this->build());
    }
    
}
