<?php

namespace Omnipay\WestpacPaywayRest\Message;

use Mockery;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = Mockery::mock('\Omnipay\WestpacPaywayRest\Message\AbstractRequest')->makePartial();
        $this->request->initialize();
    }

    public function testApiKeyPublic()
    {
        $this->assertSame($this->request, $this->request->setApiKeyPublic('abc123'));
        $this->assertSame('abc123', $this->request->getApiKeyPublic());
    }

    public function testApiKeySecret()
    {
        $this->assertSame($this->request, $this->request->setApiKeySecret('abc123'));
        $this->assertSame('abc123', $this->request->getApiKeySecret());
    }

    public function testMerchantId()
    {
        $this->assertSame($this->request, $this->request->setMerchantId('abc123'));
        $this->assertSame('abc123', $this->request->getMerchantId());
    }

    public function testUseSecretKey()
    {
        $this->assertSame($this->request, $this->request->setUseSecretKey('abc123'));
        $this->assertSame('abc123', $this->request->getUseSecretKey());
    }

    public function testSingleUseTokenId()
    {
        $this->assertSame($this->request, $this->request->setSingleUseTokenId('abc123'));
        $this->assertSame('abc123', $this->request->getSingleUseTokenId());
    }

    public function testIdempotencyKey()
    {
        $this->assertSame($this->request, $this->request->setIdempotencyKey('abc123'));
        $this->assertSame('abc123', $this->request->getIdempotencyKey());
    }
}
