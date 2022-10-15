<?php

namespace PromptPayQR;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;

/**
 * Inspired and code logic from https://github.com/dtinth/promptpay-qr
 * More information https://www.blognone.com/node/95133
 */
class Generator
{
    protected const ID_PAYLOAD_FORMAT = '00';

    protected const PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE = '01';

    protected const ID_POI_METHOD = '01';

    protected string $pointOfInitiationMethod;

    public const POI_METHOD_STATIC = '11'; // shown for more than one transaction

    public const POI_METHOD_DYNAMIC = '12'; // a new QR Code is shown for each transaction.

    protected string $merchantType;

    public const MERCHANT_TYPE_CREDIT_TRANSFER = '29';

    public const MERCHANT_TYPE_BILL_PAYMENT = '30';

    protected const MERCHANT_INFORMATION_TEMPLATE_ID_GUID = '00';

    protected string $aid;

    protected const AID_CREDIT_TRANSFER_MERCHANT_PRESENTED = 'A000000677010111'; // default

    protected const AID_CREDIT_TRANSFER_CUSTOMER_PRESENTED = 'A000000677010114';

    protected const AID_BILL_PAYMENT_DOMESTIC = 'A000000677010112'; // default

    protected const AID_BILL_PAYMENT_CROSS_BORDER = 'A000000677012006';

    /** @var string[] */
    protected array $target;

    protected const CREDIT_TRANSFER_MERCHANT_TYPE_PHONE_NUMBER = '01';

    protected const CREDIT_TRANSFER_MERCHANT_TYPE_NATIONAL_ID = '02';

    protected const CREDIT_TRANSFER_MERCHANT_TYPE_EWALLET_ID = '03';

    protected const BILL_PAYMENT_BILLER_ID = '01';

    protected const BILL_PAYMENT_REF_1 = '02';

    protected const BILL_PAYMENT_REF_2 = '03';

    protected const ID_TRANSACTION_CURRENCY = '53';

    protected const TRANSACTION_CURRENCY_THB = '764';

    protected const ID_TRANSACTION_AMOUNT = '54';

    protected ?float $amount;

    protected const ID_COUNTRY_CODE = '58';

    protected const COUNTRY_CODE_TH = 'TH';

    protected const ID_CRC = '63';

    public function __construct(string $pointOfInitiationMethod, string $merchantType)
    {
        $this->pointOfInitiationMethod = $pointOfInitiationMethod;
        $this->merchantType = $merchantType;
    }

    public function setAmount(?float $amount = null): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Build QR content
     *
     * @throws Exception
     */
    public function build(): string
    {
        if (empty($this->target)) {
            throw new Exception('Transfer target is required.');
        }

        $data = [
            Helper::f(self::ID_PAYLOAD_FORMAT, self::PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE),
            Helper::f(self::ID_POI_METHOD, $this->pointOfInitiationMethod),
            Helper::f($this->merchantType, Helper::serialize($this->target)),
            Helper::f(self::ID_COUNTRY_CODE, self::COUNTRY_CODE_TH),
            Helper::f(self::ID_TRANSACTION_CURRENCY, self::TRANSACTION_CURRENCY_THB),
        ];
        if (! empty($this->amount)) {
            // Caution: amount 0.00 will be treated as static QR
            $data[] = Helper::f(self::ID_TRANSACTION_AMOUNT, Helper::formatAmount($this->amount));
        }
        $data[] = Helper::f(self::ID_CRC, Helper::crc16(Helper::serialize($data).self::ID_CRC.'04'));

        return Helper::serialize($data);
    }

    protected static function getWriter(int $width): Writer
    {
        $renderer = new ImageRenderer(new RendererStyle($width), new SvgImageBackEnd());

        return new Writer($renderer);
    }

    /**
     * @throws Exception
     */
    public function toSvgFile(string $savePath, int $width = 500): void
    {
        self::getWriter($width)->writeFile($this->build(), $savePath);
    }

    /**
     * @throws Exception
     */
    public function toSvgString(int $width = 500): string
    {
        return self::getWriter($width)->writeString($this->build());
    }
}
