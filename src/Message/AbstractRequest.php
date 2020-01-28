<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

abstract class AbstractRequest extends BaseAbstractRequest
{

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

    public function getBillName()
    {
        return $this->getParameter('billName');
    }

    public function setBillName($value)
    {
        return $this->setParameter('billName', $value);
    }

    public function getBillDescription()
    {
        return $this->getParameter('billDescription');
    }

    public function setBillDescription($value)
    {
        return $this->setParameter('billDescription', $value);
    }

    public function getBillPriceSetting()
    {
        return $this->getParameter('billPriceSetting');
    }

    public function setBillPriceSetting($value)
    {
        return $this->setParameter('billPriceSetting', $value);
    }

    public function getBillPayorInfo()
    {
        return $this->getParameter('billPayorInfo');
    }

    public function setBillPayorInfo($value)
    {
        return $this->setParameter('billPayorInfo', $value);
    }

    public function getBillAmount()
    {
        return $this->getParameter('billAmount')*100;
    }

    public function setBillAmount($value)
    {
        return $this->setParameter('billAmount', $value);
    }

    public function getBillReturnUrl()
    {
        return $this->getParameter('billReturnUrl');
    }

    public function setBillReturnUrl($value)
    {
        return $this->setParameter('billReturnUrl', $value);
    }

    public function getBillCallbackUrl()
    {
        return $this->getParameter('billCallbackUrl');
    }

    public function setBillCallbackUrl($value)
    {
        return $this->setParameter('billCallbackUrl', $value);
    }

    public function getBillExternalReferenceNo()
    {
        return $this->getParameter('billExternalReferenceNo');
    }

    public function setBillExternalReferenceNo($value)
    {
        return $this->setParameter('billExternalReferenceNo', $value);
    }

    public function getBillTo()
    {
        return $this->getParameter('billTo');
    }

    public function setBillTo($value)
    {
        return $this->setParameter('billTo', $value);
    }

    public function getBillEmail()
    {
        return $this->getParameter('billEmail');
    }

    public function setBillEmail($value)
    {
        return $this->setParameter('billEmail', $value);
    }

    public function getBillPhone()
    {
        return $this->getParameter('billPhone');
    }

    public function setBillPhone($value)
    {
        return $this->setParameter('billPhone', $value);
    }

    public function getBillSplitPayment()
    {
        return $this->getParameter('billSplitPayment');
    }

    public function setBillSplitPayment($value)
    {
        return $this->setParameter('billSplitPayment', $value);
    }

    public function getBillSplitPaymentArgs()
    {
        return $this->getParameter('billSplitPaymentArgs');
    }

    public function setBillSplitPaymentArgs($value)
    {
        return $this->setParameter('billSplitPaymentArgs', $value);
    }

    public function getBillPaymentChannel()
    {
        return $this->getParameter('billPaymentChannel');
    }

    public function setBillPaymentChannel($value)
    {
        return $this->setParameter('billPaymentChannel', $value);
    }

    public function getBillDisplayMerchant()
    {
        return $this->getParameter('billDisplayMerchant');
    }

    public function setBillDisplayMerchant($value)
    {
        return $this->setParameter('billDisplayMerchant', $value);
    }

    public function getBillContentEmail()
    {
        return $this->getParameter('billContentEmail');
    }

    public function setBillContentEmail($value)
    {
        return $this->setParameter('billContentEmail', $value);
    }

    public function getBillCode()
    {
        return $this->getParameter('billCode');
    }

    public function setBillCode($value)
    {
        return $this->setParameter('billCode', $value);
    }
}