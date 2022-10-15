<?php

namespace PromptPayQR;

class BillPayment extends Generator
{
    public function __construct(string $pointOfInitiationMethod)
    {
        parent::__construct($pointOfInitiationMethod, self::MERCHANT_TYPE_BILL_PAYMENT);
        $this->aid = self::AID_BILL_PAYMENT_DOMESTIC;
    }

    public function domestic(): BillPayment
    {
        $this->aid = self::AID_BILL_PAYMENT_DOMESTIC;

        return $this;
    }

    public function crossBorder(): BillPayment
    {
        $this->aid = self::AID_BILL_PAYMENT_CROSS_BORDER;

        return $this;
    }

    public function setBillerIdentifier(string $billerId, string $ref1, ?string $ref2 = null): BillPayment
    {
        $this->target = [
            Helper::f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, $this->aid),
            Helper::f(self::BILL_PAYMENT_BILLER_ID, Helper::formatTarget($billerId)),
            Helper::f(self::BILL_PAYMENT_REF_1, $ref1),
        ];
        if ($ref2) {
            $this->target[] = Helper::f(self::BILL_PAYMENT_REF_2, $ref2);
        }

        return $this;
    }
}
