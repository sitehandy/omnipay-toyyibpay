<?php

namespace Omnipay\ToyyibPay;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\ToyyibPay\Gateway;
use Omnipay\Common\Exception\InvalidRequestException;

class GatewayTest extends GatewayTestCase
{
    public $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

}