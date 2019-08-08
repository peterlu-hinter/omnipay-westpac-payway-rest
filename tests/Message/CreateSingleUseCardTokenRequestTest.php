<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 18/06/17
 * Time: 21:30
 */

namespace Omnipay\WestpacPaywayRest\Test\Message;

use Omnipay\Tests\TestCase;
use Omnipay\WestpacPaywayRest\Message\CreateSingleUseCardTokenRequest;

class CreateSingleUseCardTokenRequestTest extends TestCase
{
    /**
     * @var CreateSingleUseCardTokenRequest $request
     */
    private $request;

    public function setUp()
    {
        parent::setUp();
        $this->request = new CreateSingleUseCardTokenRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testEndpoint()
    {
        $this->assertSame('https://api.payway.com.au/rest/v1/single-use-tokens', $this->request->getEndpoint());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage You must pass a "card" parameter.
     */
    public function testGetDataInvalid()
    {
        $this->request->setCard(null);

        $this->request->getData();
    }

    public function testGetDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);

        $data = $this->request->getData();

        $expiryMonth = sprintf('%02d', $card['expiryMonth']);
        $name = $card['firstName'] . ' ' . $card['lastName'];

        $this->assertEquals('creditCard',        $data['paymentMethod']);
        $this->assertEquals($card['number'],     $data['cardNumber']);
        $this->assertEquals($name,               $data['cardholderName']);
        $this->assertEquals($card['cvv'],        $data['cvn']);
        $this->assertEquals($expiryMonth,        $data['expiryDateMonth']);
        $this->assertEquals($card['expiryYear'], $data['expiryDateYear']);
    }
}
