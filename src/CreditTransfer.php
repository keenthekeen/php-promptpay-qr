<?php

namespace PromptPayQR;

use Exception;

class CreditTransfer extends Generator
{
    public function __construct(string $pointOfInitiationMethod)
    {
        parent::__construct($pointOfInitiationMethod, self::MERCHANT_TYPE_CREDIT_TRANSFER);
        $this->aid = self::AID_CREDIT_TRANSFER_MERCHANT_PRESENTED;
    }

    public function merchantPresented(): CreditTransfer
    {
        $this->aid = self::AID_CREDIT_TRANSFER_MERCHANT_PRESENTED;

        return $this;
    }

    public function customerPresented(): CreditTransfer
    {
        $this->aid = self::AID_CREDIT_TRANSFER_CUSTOMER_PRESENTED;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function setMerchantIdentifier(string $identifier, ?string $type = null): CreditTransfer
    {
        if ($type == null) {
            $idLength = strlen(Helper::sanitizeTarget($identifier));
            switch ($idLength) {
                case 13:
                    $type = self::CREDIT_TRANSFER_MERCHANT_TYPE_NATIONAL_ID;
                    break;
                case 15:
                    $type = self::CREDIT_TRANSFER_MERCHANT_TYPE_EWALLET_ID;
                    break;
                case 10:
                case 9:
                    $type = self::CREDIT_TRANSFER_MERCHANT_TYPE_PHONE_NUMBER;
                    break;
                default:
                    throw new Exception('Unable to guess merchant type due to invalid identifier length');
            }
        }
        $this->target = [
            Helper::f(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, $this->aid),
            Helper::f($type, Helper::formatTarget($identifier)),
        ];

        return $this;
    }

    public function phoneNumber(string $phoneNumber): CreditTransfer
    {
        $this->setMerchantIdentifier(self::CREDIT_TRANSFER_MERCHANT_TYPE_PHONE_NUMBER, $phoneNumber);

        return $this;
    }

    public function nationalId(string $nationalId): CreditTransfer
    {
        $this->setMerchantIdentifier(self::CREDIT_TRANSFER_MERCHANT_TYPE_NATIONAL_ID, $nationalId);

        return $this;
    }

    public function eWallet(string $eWalletId): CreditTransfer
    {
        $this->setMerchantIdentifier(self::CREDIT_TRANSFER_MERCHANT_TYPE_EWALLET_ID, $eWalletId);

        return $this;
    }
}
