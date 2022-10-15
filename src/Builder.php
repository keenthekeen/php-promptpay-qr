<?php

namespace PromptPayQR;

class Builder
{
    protected string $pointOfInitiationMethod;

    protected function __construct(string $pointOfInitiationMethod)
    {
        $this->pointOfInitiationMethod = $pointOfInitiationMethod;
    }

    /**
     * Static QR: for multiple use
     */
    public static function staticQR(): Builder
    {
        return new self(Generator::POI_METHOD_STATIC);
    }

    /**
     * Dynamic QR: for one-time use
     */
    public static function dynamicQR(): Builder
    {
        return new self(Generator::POI_METHOD_DYNAMIC);
    }

    /**
     * Tag 29: PromptPay - Credit Transfer with PromptPayID
     */
    public function creditTransfer(): CreditTransfer
    {
        return new CreditTransfer($this->pointOfInitiationMethod);
    }

    /**
     * Tag 30: PromptPay - Bill Payment
     */
    public function billPayment(): BillPayment
    {
        return new BillPayment($this->pointOfInitiationMethod);
    }

    /**
     * @throws \Exception
     */
    public static function staticMerchantPresentedQR(string $merchantId): CreditTransfer
    {
        $generator = new CreditTransfer(Generator::POI_METHOD_STATIC);
        $generator->setMerchantIdentifier($merchantId);

        return $generator;
    }
}
