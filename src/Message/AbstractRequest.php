<?php

namespace Omnipay\WestpacPaywayRest\Message;

/**
 * @link https://www.payway.com.au/rest-docs/index.html
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /** @var string Endpoint URL */
    protected $endpoint = 'https://api.payway.com.au/rest/v1';

    abstract public function getEndpoint();

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
     * Get Use Secret Key setting
     * @return bool Use secret API key if true
     */
    public function getUseSecretKey()
    {
        return $this->getParameter('useSecretKey');
    }

    /**
     * Set Use Secret Key setting
     * @param  string $value Flag to use secret key
     */
    public function setUseSecretKey($value)
    {
        return $this->setParameter('useSecretKey', $value);
    }

    /**
     * Get single-use token
     * @return string Token key
     */
    public function getSingleUseTokenId()
    {
        return $this->getParameter('singleUseTokenId');
    }

    /**
     * Set single-use token
     * @param  string $value Token Key
     */
    public function setSingleUseTokenId($value)
    {
        return $this->setParameter('singleUseTokenId', $value);
    }

    /**
     * Get Idempotency Key
     * @return string Idempotency Key
     */
    public function getIdempotencyKey()
    {
        return $this->getParameter('idempotencyKey') ?: uniqid();
    }

    /**
     * Set Idempotency Key
     * @param  string $value Idempotency Key
     */
    public function setIdempotencyKey($value)
    {
        return $this->setParameter('idempotencyKey', $value);
    }

    public function getCustomerNumber()
    {
        return $this->getParameter('customerNumber');
    }

    public function setCustomerNumber($value)
    {
        return $this->setParameter('customerNumber', $value);
    }

    public function getTransactionType()
    {
        return $this->getParameter('transactionType');
    }

    public function setTransactionType($value)
    {
        return $this->setParameter('transactionType', $value);
    }

    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }

    public function getPrincipalAmount()
    {
        return $this->getParameter('principalAmount');
    }

    public function setPrincipalAmount($value)
    {
        return $this->setParameter('principalAmount', $value);
    }

    public function getCurrency()
    {
        // PayWay expects lowercase currency values
        return ($this->getParameter('currency'))
            ? strtolower($this->getParameter('currency'))
            : null;
    }

    public function setCurrency($value)
    {
        return $this->setParameter('currency', $value);
    }

    public function getOrderNumber()
    {
        return $this->getParameter('orderNumber');
    }

    public function setOrderNumber($value)
    {
        return $this->setParameter('orderNumber', $value);
    }

    public function getCustomerIpAddress()
    {
        return $this->getParameter('customerIpAddress');
    }

    public function setCustomerIpAddress($value)
    {
        return $this->setParameter('customerIpAddress', $value);
    }

    /**
     * Get HTTP method
     * @return string HTTP method (GET, PUT, etc)
     */
    public function getHttpMethod()
    {
        return 'GET';
    }

    /**
     * Get request headers
     * @return array Request headers
     */
    public function getRequestHeaders()
    {
        // common headers
        $headers = array(
            'Accept' => 'application/json',
        );

        // set content type
        if ($this->getHttpMethod() !== 'GET') {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        // prevent duplicate POSTs
        if ($this->getHttpMethod() === 'POST') {
            $headers['Idempotency-Key'] = $this->getIdempotencyKey();
        }

        return $headers;
    }

    /**
     * Send data request
     *
     * @param $data
     *
     * @return \Omnipay\Common\Message\ResponseInterface|\Omnipay\WestpacPaywayRest\Message\Response
     */
    public function sendData($data)
    {
        // get the appropriate API key
        $apiKey = ($this->getUseSecretKey()) ? $this->getApiKeySecret() : $this->getApiKeyPublic();

        $headers = $this->getRequestHeaders();
        $headers['Authorization'] = 'Basic ' . base64_encode($apiKey . ':');

        $body = $data ? http_build_query($data, '', '&') : null;

        $response = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            $headers,
            $body,
            '1.2' // Enforce TLS v1.2
        );

        $this->response = new Response($this, json_decode($response->getBody()->getContents(), true));

        // save additional info
        $this->response->setHttpResponseCode($response->getStatusCode());
        $this->response->setTransactionType($this->getTransactionType());

        return $this->response;
    }
}
