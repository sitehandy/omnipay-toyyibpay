<?php

namespace Omnipay\ToyyibPay;

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
            'userSecretKey' => '',
            'categoryCode' => ''
            'testMode' => false,
        );
    }

    public function getUserSecretKey()
    {
        return $this->getParameter('userSecretKey');
    }

    public function setUserSecretKey($value)
    {
        return $this->setParameter('userSecretKey', $value);
    }

    public function getCategoryCode()
    {
        return $this->getParameter('categoryCode');
    }

    public function setCategoryCode($value)
    {
        return $this->setParameter('categoryCode', $value);
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
