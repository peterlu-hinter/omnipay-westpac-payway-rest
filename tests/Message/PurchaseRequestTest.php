<?php

namespace Omnipay\WestpacPaywayRest\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /**
     * @var \Omnipay\WestpacPaywayRest\Message\PurchaseRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize([
            'amount' => '10.00',
            'currency' => 'USD',
        ]);
    }

    public function testGetData()
    {
        $this->request->setCustomerNumber('ABC123');
        $this->request->setOrderNumber('456');
        $this->request->setSingleUseTokenId('EFG789');

        $data = $this->request->getData();

        $this->assertEquals('payment', $data['transactionType']);
        $this->assertEquals('10.00',   $data['principalAmount']);
        $this->assertEquals('usd',     $data['currency']);
        $this->assertEquals('ABC123',  $data['customerNumber']);
        $this->assertEquals('456',     $data['orderNumber']);
        $this->assertEquals('EFG789',  $data['singleUseTokenId']);
    }
}
