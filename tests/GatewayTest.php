<?php

namespace Omnipay\PaywayRest\Test;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\PaywayRest\Gateway;

/**
 * @property Gateway gateway
 */
class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setTestMode(true);
    }

    public function testCreateToken()
    {
        $request = $this->gateway->createSingleUseCardToken([
            'card' => new CreditCard([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'number' => '424242424242',
                'expiryMonth' => '03',
                'expiryYear' => '2020',
                'cvv' => '123',
            ]),
        ]);

        $this->assertInstanceOf('Omnipay\PaywayRest\Message\CreateSingleUseCardTokenRequest', $request);
        $data = $request->getData();

        $this->assertEquals('creditCard',           $data['paymentMethod']);
        $this->assertEquals('424242424242',         $data['cardNumber']);
        $this->assertEquals('John Doe',             $data['cardholderName']);
        $this->assertEquals('123',                  $data['cvn']);
        $this->assertEquals('03',                   $data['expiryDateMonth']);
        $this->assertEquals('2020',                 $data['expiryDateYear']);
    }

    public function testPurchaseUsingStringAmount()
    {
        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'AUD',
            'customerNumber' => 'ABC123',
            'orderNumber' => '456',
            'singleUseTokenId' => 'EFG789',
        ]);

        $this->assertInstanceOf('Omnipay\PaywayRest\Message\PurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());

        $data = $request->getData();

        $this->assertEquals('payment', $data['transactionType']);
        $this->assertEquals('10.00',   $data['principalAmount']);
        $this->assertEquals('aud',     $data['currency']);
        $this->assertEquals('ABC123',  $data['customerNumber']);
        $this->assertEquals('456',     $data['orderNumber']);
        $this->assertEquals('EFG789',  $data['singleUseTokenId']);
    }

    public function testPurchaseUsingMoney()
    {
        $request = $this->gateway->purchase([
            'currency' => 'AUD',
            'customerNumber' => 'ABC123',
            'orderNumber' => '456',
            'singleUseTokenId' => 'EFG789',
        ]);

        $money = new Money(1000, new Currency('AUD'));

        $request->setMoney($money);

        $this->assertInstanceOf('Omnipay\PaywayRest\Message\PurchaseRequest', $request);
        $this->assertSame($money, $request->getAmount());
        $this->assertSame('10.00', (new DecimalMoneyFormatter(new ISOCurrencies()))->format($request->getAmount()));

        $data = $request->getData();

        $this->assertEquals('payment', $data['transactionType']);
        $this->assertEquals('10.00',   $data['principalAmount']);
        $this->assertEquals('aud',     $data['currency']);
        $this->assertEquals('ABC123',  $data['customerNumber']);
        $this->assertEquals('456',     $data['orderNumber']);
        $this->assertEquals('EFG789',  $data['singleUseTokenId']);
    }
}
