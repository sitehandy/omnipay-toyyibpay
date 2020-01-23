<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class CompleteCreateBillResponse extends AbstractResponse implements RedirectResponseInterface
{
    protected $baseEndpoint = 'https://toyyibpay.com/';

    /*
     * Payment status
     * 1 - Successful transaction
     * 2 - Pending transaction
     * 3 - Unsuccessful transaction
     * 4 - Pending
     */
    public function isSuccessful()
    {
        return (isset($this->data['billStatus']) && $this->data['billStatus'] == 1) ? true : false;
    }

    public function getTransactionReference()
    {
        return isset($this->data['billPermalink']) ? $this->data['billPermalink'] : null;
    }
}