<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class CompleteCreateBillResponse extends AbstractResponse implements RedirectResponseInterface
{
    /*
     * Payment status
     * 1 - Successful transaction
     * 2 - Pending transaction
     * 3 - Unsuccessful transaction
     * 4 - Pending
     */
    public function isSuccessful()
    {
        return $this->data['billpaymentStatus'] == 1 ? true : false;
    }

    public function getTransactionReference()
    {
        return $this->data['billpaymentInvoiceNo'] ? $this->data['billCode'] : null;
    }

    public function getTransactionId()
    {
        return $this->data['billCode'];
    }

    public function getMessage()
    {
        return 'Sorry, there was an error processing your payment. Please try again later.';
    }

    public function isRedirect()
    {
        return $this->data['billpaymentStatus'] != 1 ? true : false;
    }

    public function getRedirectUrl()
    {
        return $this->data['redirectUrl'];
    }
}