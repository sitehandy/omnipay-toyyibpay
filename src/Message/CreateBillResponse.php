<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class CreateBillResponse extends AbstractResponse implements RedirectResponseInterface
{
    protected $baseEndpoint = 'https://toyyibpay.com/';

    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return isset($this->data[0]['BillCode']) ? true : false;
    }

    public function getRedirectUrl()
    {
        return $this->baseEndpoint . $this->data[0]['BillCode'];
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return $this->data;
    }

    public function getTransactionReference()
    {
        return isset($this->data[0]['BillCode']) ? $this->data[0]['BillCode'] : null;
    }
}