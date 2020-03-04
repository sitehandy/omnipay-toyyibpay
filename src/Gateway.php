<?php

namespace Omnipay\ToyyibPay;

use Omnipay\Common\AbstractGateway;

/**
 * toyyibPay Payment Gateway
 *
 * @link https://toyyibpay.com/apireference/
 *
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'toyyibPay';
    }

    public function getDefaultParameters()
    {
        return array(
            'testMode' => false
        );
    }

    /**
     * Authorize and capture a payment.
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\ToyyibPay\Message\PurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\ToyyibPay\Message\CompletePurchaseRequest', $parameters);
    }
}
