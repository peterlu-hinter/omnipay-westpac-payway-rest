<?php

namespace Omnipay\WestpacPaywayRest\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * @link https://www.payway.com.au/docs/rest.html#tokenise-a-credit-card
 */
class CreateSingleUseCardTokenRequest extends AbstractRequest
{
    public function getData()
    {
        if (!$this->getParameter('card')) {
            throw new InvalidRequestException('You must pass a "card" parameter.');
        }

        $this->getCard()->validate();

        // PayWay requires two digit expiry month.
        $expiryDateMonth = str_pad($this->getCard()->getExpiryMonth(), 2, 0, STR_PAD_LEFT);

        return [
            'paymentMethod' => 'creditCard',
            'cardNumber' => $this->getCard()->getNumber(),
            'cardholderName' => $this->getCard()->getName(),
            'cvn' => $this->getCard()->getCvv(),
            'expiryDateMonth' => $expiryDateMonth,
            'expiryDateYear' => $this->getCard()->getExpiryYear(),
        ];
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint . '/single-use-tokens';
    }

    public function getHttpMethod()
    {
        return 'POST';
    }
}
