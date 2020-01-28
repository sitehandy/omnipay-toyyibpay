<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

class CreateBillResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return isset($this->data['BillCode']) ? true : false;
    }

    public function getRedirectUrl()
    {
        return $this->data['redirectUrl'];
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return $this->getData();
    }

    public function getTransactionReference()
    {
        return isset($this->data['BillCode']) ? $this->data['BillCode'] : null;
    }

    public function getMessage()
    {
        return 'Sorry, there was an error processing your payment. Please try again later.';
    }
}