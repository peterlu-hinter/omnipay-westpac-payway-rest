<?php

namespace Omnipay\PaywayRest;

use Omnipay\Common\AbstractGateway;
use Omnipay\PaywayRest\Message\CreateSingleUseCardTokenRequest;
use Omnipay\PaywayRest\Message\PurchaseRequest;

/**
 * @method \Omnipay\Common\Message\RequestInterface authorize(array $options = array())         (Optional method)
 *         Authorize an amount on the customers card
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = array()) (Optional method)
 *         Handle return from off-site gateways after authorization
 * @method \Omnipay\Common\Message\RequestInterface capture(array $options = array())           (Optional method)
 *         Capture an amount you have previously authorized
 * @method \Omnipay\Common\Message\RequestInterface completePurchase(array $options = array())  (Optional method)
 *         Handle return from off-site gateways after purchase
 * @method \Omnipay\Common\Message\RequestInterface refund(array $options = array())            (Optional method)
 *         Refund an already processed transaction
 * @method \Omnipay\Common\Message\RequestInterface void(array $options = array())              (Optional method)
 *         Generally can only be called up to 24 hours after submitting a transaction
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = array())        (Optional method)
 *         The returned response object includes a cardReference, which can be used for future transactions
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = array())        (Optional method)
 *         Update a stored card
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = array())        (Optional method)
 *         Delete a stored card
 */
class Gateway extends AbstractGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     * @return string
     */
    public function getName()
    {
        return 'Westpac PayWay REST API';
    }

    /**
     * Get gateway short name
     *
     * This name can be used with GatewayFactory as an alias of the gateway class,
     * to create new instances of this gateway.
     * @return string
     */
    public function getShortName()
    {
        return 'PayWay';
    }

    public function getDefaultParameters()
    {
        return array(
            'apiKeyPublic' => '',
            'apiKeySecret' => '',
            'merchantId'   => '',
        );
    }

    /**
     * Get API publishable key
     * @return string
     */
    public function getApiKeyPublic()
    {
        return $this->getParameter('apiKeyPublic');
    }

    /**
     * Set API publishable key
     * @param  string $value API publishable key
     */
    public function setApiKeyPublic($value)
    {
        return $this->setParameter('apiKeyPublic', $value);
    }

    /**
     * Get API secret key
     * @return string
     */
    public function getApiKeySecret()
    {
        return $this->getParameter('apiKeySecret');
    }

    /**
     * Set API secret key
     * @param  string $value API secret key
     */
    public function setApiKeySecret($value)
    {
        return $this->setParameter('apiKeySecret', $value);
    }

    /**
     * Get Merchant
     * @return string Merchant ID
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * Set Merchant
     * @param  string $value Merchant ID
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return bool
     */
    public function getUseSecretKey()
    {
        return false;
    }

    /**
     * Purchase request
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\PurchaseRequest|\Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * Create singleUseTokenId with a CreditCard
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\CreateSingleUseCardTokenRequest|\Omnipay\Common\Message\AbstractRequest
     */
    public function createSingleUseCardToken(array $parameters = array())
    {
        return $this->createRequest(CreateSingleUseCardTokenRequest::class, $parameters);
    }

        /**
     * Get List of Transactions by receiptNumber (stored in OmniPay as transactionReference)
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\TransactionsRequest
     */
    public function getTransactions(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\TransactionsRequest', $parameters);
    }

    /**
     * Refund (or Void) request
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        // note that transactionReference is not reliable, so we do an extra lookup.
        $refundParams = [
            'principalAmount' => $parameters['amount'],
            'parentTransactionId' => $parameters['transactionReference']
        ];
        $voidParams = [];
        $transactions = $this->getTransactions($parameters);
        $response = $transactions->send();
        $data = $response->getData('data');
        if ($data && isset($data[0]) && isset($data[0]['transactionId'])) {
            $refundParams['parentTransactionId'] = $data[0]['transactionId'];
            $voidParams['transactionId'] = $data[0]['transactionId'];

        }
        // note that transaction might not be refundable, so we do an extra lookup.
        $transaction = $this->getTransactionDetails(['transactionId' => $data[0]['transactionId']]);
        $response = $transaction->send();
        $canRefund = $response->getData('isRefundable');
        $canVoid = $response->getData('isVoidable');
        if ($canRefund) {
            return $this->createRequest('\Omnipay\PaywayRest\Message\RefundRequest', $refundParams);
        } elseif ($canVoid) {
            return $this->createRequest('\Omnipay\PaywayRest\Message\VoidRequest', $voidParams);
        }

    }
}