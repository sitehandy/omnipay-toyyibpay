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

    public function createBill(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\ToyyibPay\Message\CreateBillRequest', $parameters);
    }

    public function completeCreateBill(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\ToyyibPay\Message\CompleteCreateBillRequest', $parameters);
    }
}
