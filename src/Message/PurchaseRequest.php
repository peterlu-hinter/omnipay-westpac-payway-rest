<?php

namespace Omnipay\WestpacPaywayRest\Message;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

/**
 * @link https://www.payway.com.au/rest-docs/index.html#process-a-payment
 */
class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate(
            'customerNumber',
            'amount',
            'currency'
        );

        $data = array(
            'customerNumber'  => $this->getCustomerNumber(),
            'transactionType' => 'payment',
            'currency'        => $this->getCurrency(),
        );

        // Has the Money class been used to set the amount?
        if ($this->getAmount() instanceof Money) {
            // Ensure principal amount is formatted as decimal string
            $data['principalAmount'] = (new DecimalMoneyFormatter(new ISOCurrencies()))->format($this->getAmount());
        } else {
            $data['principalAmount'] = $this->getAmount();
        }

        if ($this->getOrderNumber()) {
            $data['orderNumber'] = $this->getOrderNumber();
        }
        if ($this->getMerchantId()) {
            $data['merchantId'] = $this->getMerchantId();
        }
        if ($this->getBankAccountId()) {
            $data['bankAccountId'] = $this->getBankAccountId();
        }
        if ($this->getSingleUseTokenId()){
            $data['singleUseTokenId'] = $this->getSingleUseTokenId();
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/transactions';
    }

    public function getHttpMethod()
    {
        return 'POST';
    }

    public function getUseSecretKey()
    {
        return true;
    }
}
