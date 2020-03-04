<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * toyyibPay Create a Bill Response
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * The initial Server response is never complete without
     * redirecting the user.
     *
     * @return bool false
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * Only redirect if the status indicates the pre-auth details are acceptable.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return isset($this->data['BillCode']) ? true : false;
    }

    /**
     * @return string|null URL if present
     */
    public function getRedirectUrl()
    {
        return isset($this->data['redirectUrl']) ? $this->data['redirectUrl'] : null;
    }

    /**
     * @return array empy array; all the data is in the GET URL
     */
    public function getRedirectData()
    {
        return [];
    }

    /**
     * @return string Always GET
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getMessage()
    {
        return 'Sorry, there was an error in creating your bill payment. Please try again later or contact administrator for further assistance.';
    }
}
